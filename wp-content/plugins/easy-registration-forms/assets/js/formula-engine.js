var erformsFrmlEngine = {};
(function(){

function Rule(name, symbols, postprocess) {
    this.id = ++Rule.highestId;
    this.name = name;
    this.symbols = symbols; // a list of literal | regex class | nonterminal
    this.postprocess = postprocess;
    return this;
}
Rule.highestId = 0;

Rule.prototype.toString = function(withCursorAt) {
    function symbolToString(e) {
        return e.literal ? JSON.stringify(e.literal) : e.type ? '%' + e.type : e.toString();
    }
    var symbolSequence =
        typeof withCursorAt === 'undefined'
            ? this.symbols.map(symbolToString).join(' ')
            : this.symbols
                  .slice(0, withCursorAt)
                  .map(symbolToString)
                  .join(' ') +
              ' ● ' +
              this.symbols
                  .slice(withCursorAt)
                  .map(symbolToString)
                  .join(' ');
    return this.name + ' → ' + symbolSequence;
};

// a State is a rule at a position from a given starting point in the input stream (reference)
function State(rule, dot, reference, wantedBy) {
    this.rule = rule;
    this.dot = dot;
    this.reference = reference;
    this.data = [];
    this.wantedBy = wantedBy;
    this.isComplete = this.dot === rule.symbols.length;
}

State.prototype.toString = function() {
    return '{' + this.rule.toString(this.dot) + '}, from: ' + (this.reference || 0);
};

State.prototype.nextState = function(child) {
    var state = new State(this.rule, this.dot + 1, this.reference, this.wantedBy);
    state.left = this;
    state.right = child;
    if (state.isComplete) {
        state.data = state.build();
    }
    return state;
};

State.prototype.build = function() {
    var children = [];
    var node = this;
    do {
        children.push(node.right.data);
        node = node.left;
    } while (node.left);
    children.reverse();
    return children;
};

State.prototype.finish = function() {
    if (this.rule.postprocess) {
        this.data = this.rule.postprocess(this.data, this.reference, Parser.fail);
    }
};

function Column(grammar, index) {
    this.grammar = grammar;
    this.index = index;
    this.states = [];
    this.wants = {}; // states indexed by the non-terminal they expect
    this.scannable = []; // list of states that expect a token
    this.completed = {}; // states that are nullable
}

Column.prototype.process = function(nextColumn) {
    var states = this.states;
    var wants = this.wants;
    var completed = this.completed;

    for (var w = 0; w < states.length; w++) {
        // nb. we push() during iteration
        var state = states[w];

        if (state.isComplete) {
            state.finish();
            if (state.data !== Parser.fail) {
                // complete
                var wantedBy = state.wantedBy;
                for (var i = wantedBy.length; i--; ) {
                    // this line is hot
                    var left = wantedBy[i];
                    this.complete(left, state);
                }

                // special-case nullables
                if (state.reference === this.index) {
                    // make sure future predictors of this rule get completed.
                    var exp = state.rule.name;
                    (this.completed[exp] = this.completed[exp] || []).push(state);
                }
            }
        } else {
            // queue scannable states
            var exp = state.rule.symbols[state.dot];
            if (typeof exp !== 'string') {
                this.scannable.push(state);
                continue;
            }

            // predict
            if (wants[exp]) {
                wants[exp].push(state);

                if (completed.hasOwnProperty(exp)) {
                    var nulls = completed[exp];
                    for (var i = 0; i < nulls.length; i++) {
                        var right = nulls[i];
                        this.complete(state, right);
                    }
                }
            } else {
                wants[exp] = [state];
                this.predict(exp);
            }
        }
    }
};

Column.prototype.predict = function(exp) {
    var rules = this.grammar.byName[exp] || [];

    for (var i = 0; i < rules.length; i++) {
        var r = rules[i];
        var wantedBy = this.wants[exp];
        var s = new State(r, 0, this.index, wantedBy);
        this.states.push(s);
    }
};

Column.prototype.complete = function(left, right) {
    var copy = left.nextState(right);
    this.states.push(copy);
};

function Grammar(rules, start) {
    this.rules = rules;
    this.start = start || this.rules[0].name;
    var byName = (this.byName = {});
    this.rules.forEach(function(rule) {
        if (!byName.hasOwnProperty(rule.name)) {
            byName[rule.name] = [];
        }
        byName[rule.name].push(rule);
    });
}

// So we can allow passing (rules, start) directly to Parser for backwards compatibility
Grammar.fromCompiled = function(rules, start) {
    var lexer = rules.Lexer;
    if (rules.ParserStart) {
        start = rules.ParserStart;
        rules = rules.ParserRules;
    }
    var rules = rules.map(function(r) {
        return new Rule(r.name, r.symbols, r.postprocess);
    });
    var g = new Grammar(rules, start);
    g.lexer = lexer; // nb. storing lexer on Grammar is iffy, but unavoidable
    return g;
};

function StreamLexer() {
    this.reset('');
}

StreamLexer.prototype.reset = function(data, state) {
    this.buffer = data;
    this.index = 0;
    this.line = state ? state.line : 1;
    this.lastLineBreak = state ? -state.col : 0;
};

StreamLexer.prototype.next = function() {
    if (this.index < this.buffer.length) {
        var ch = this.buffer[this.index++];
        if (ch === '\n') {
            this.line += 1;
            this.lastLineBreak = this.index;
        }
        return { value: ch };
    }
};

StreamLexer.prototype.save = function() {
    return {
        line: this.line,
        col: this.index - this.lastLineBreak,
    };
};

StreamLexer.prototype.formatError = function(token, message) {
    // nb. this gets called after consuming the offending token,
    // so the culprit is index-1
    var buffer = this.buffer;
    if (typeof buffer === 'string') {
        var nextLineBreak = buffer.indexOf('\n', this.index);
        if (nextLineBreak === -1) nextLineBreak = buffer.length;
        var line = buffer.substring(this.lastLineBreak, nextLineBreak);
        var col = this.index - this.lastLineBreak;
        message += ' at line ' + this.line + ' col ' + col + ':\n\n';
        message += '  ' + line + '\n';
        message += '  ' + Array(col).join(' ') + '^';
        return message;
    } else {
        return message + ' at index ' + (this.index - 1);
    }
};

function Parser(rules, start, options) {
    if (rules instanceof Grammar) {
        var grammar = rules;
        var options = start;
    } else {
        var grammar = Grammar.fromCompiled(rules, start);
    }
    this.grammar = grammar;

    // Read options
    this.options = {
        keepHistory: false,
        lexer: grammar.lexer || new StreamLexer(),
    };
    for (var key in options || {}) {
        this.options[key] = options[key];
    }

    // Setup lexer
    this.lexer = this.options.lexer;
    this.lexerState = undefined;

    // Setup a table
    var column = new Column(grammar, 0);
    var table = (this.table = [column]);

    // I could be expecting anything.
    column.wants[grammar.start] = [];
    column.predict(grammar.start);
    // TODO what if start rule is nullable?
    column.process();
    this.current = 0; // token index
}

// create a reserved token for indicating a parse fail
Parser.fail = {};

Parser.prototype.feed = function(chunk) {
    var lexer = this.lexer;
    lexer.reset(chunk, this.lexerState);

    var token;
    while ((token = lexer.next())) {
        // We add new states to table[current+1]
        var column = this.table[this.current];

        // GC unused states
        if (!this.options.keepHistory) {
            delete this.table[this.current - 1];
        }

        var n = this.current + 1;
        var nextColumn = new Column(this.grammar, n);
        this.table.push(nextColumn);

        // Advance all tokens that expect the symbol
        var literal = token.text !== undefined ? token.text : token.value;
        var value = lexer.constructor === StreamLexer ? token.value : token;
        var scannable = column.scannable;
        for (var w = scannable.length; w--; ) {
            var state = scannable[w];
            var expect = state.rule.symbols[state.dot];
            // Try to consume the token
            // either regex or literal
            if (expect.test ? expect.test(value) : expect.type ? expect.type === token.type : expect.literal === literal) {
                // Add it
                var next = state.nextState({ data: value, token: token, isToken: true, reference: n - 1 });
                nextColumn.states.push(next);
            }
        }

        nextColumn.process();

        // If needed, throw an error:
        if (nextColumn.states.length === 0) {
            // No states at all! This is not good.
            var message = this.lexer.formatError(token, 'invalid syntax') + '\n';
            message += 'Unexpected ' + (token.type ? token.type + ' token: ' : '');
            message += JSON.stringify(token.value !== undefined ? token.value : token) + '\n';
            var err = new Error(message);
            err.offset = this.current;
            err.token = token;
            throw err;
        }

        // maybe save lexer state
        if (this.options.keepHistory) {
            column.lexerState = lexer.save();
        }

        this.current++;
    }
    if (column) {
        this.lexerState = lexer.save();
    }

    // Incrementally keep track of results
    this.results = this.finish();

    // Allow chaining, for whatever it's worth
    return this;
};

Parser.prototype.save = function() {
    var column = this.table[this.current];
    column.lexerState = this.lexerState;
    return column;
};

Parser.prototype.restore = function(column) {
    var index = column.index;
    this.current = index;
    this.table[index] = column;
    this.table.splice(index + 1);
    this.lexerState = column.lexerState;

    // Incrementally keep track of results
    this.results = this.finish();
};

Parser.prototype.finish = function() {
    // Return the possible parsings
    var considerations = [];
    var start = this.grammar.start;
    var column = this.table[this.table.length - 1];
    column.states.forEach(function(t) {
        if (t.rule.name === start && t.dot === t.rule.symbols.length && t.reference === 0 && t.data !== Parser.fail) {
            considerations.push(t);
        }
    });
    return considerations.map(function(c) {
        return c.data;
    });
};


/************************  Nearley ends here **********************/


/************************ Expression engine **********************/
// Generated automatically by nearley, version 2.16.0
// http://github.com/Hardmath123/nearley
function id(x) { return x[0]; }

let templateResolverFn = null;
let randomNumberGenerator = null;

function setTemplateResolverFn(templateResolverFn_) {
    templateResolverFn = templateResolverFn_;
}

function setRandomGenerator(randomNumberGenerator_) {
    randomNumberGenerator = randomNumberGenerator_;
}

const resolveToken = function(token) {
    if (templateResolverFn) {
        return templateResolverFn(token);
    }
    return null;
}

const tokenRegex = /([^%]|^)%([^[%]+)%/g;
const parseTokensInString = function(str) {
    return str.replace(tokenRegex, function(match, p1, p2) {
        return p1 + resolveToken(p2);
    }).replace(/%%/g, '%');
}
let Lexer = undefined;
let ParserRules = [
    {"name": "main", "symbols": ["_", "ExpList", "_"], "postprocess": function(d) { return (d[1] && d[1]._list)? d[1]._list: d[1]; }},
    {"name": "ExpList", "symbols": ["Exp"], "postprocess": id},
    {"name": "ExpList", "symbols": ["JSFnCall"], "postprocess": id},
    {"name": "ExpList", "symbols": ["ArrayIndex"], "postprocess": id},
    {"name": "ExpList", "symbols": ["ExpList", "_", {"literal":","}, "_", "Exp"], "postprocess":  function(d) {
            if (!d[0]) {
                return {"_list": [d[0], d[4]]};
            }
            return  {
                "_list": Array.isArray(d[0]._list) ? d[0]._list.concat(d[4]) : [d[0], d[4]]
            };
        } },
    {"name": "JSFnCall", "symbols": ["ExecCall"], "postprocess": id},
    {"name": "JSFnCall$ebnf$1", "symbols": []},
    {"name": "JSFnCall$ebnf$1", "symbols": ["JSFnCall$ebnf$1", /[a-zA-Z]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "JSFnCall", "symbols": ["JSFnCall", "_", {"literal":"."}, "_", "JSFnCall$ebnf$1"], "postprocess": function(d) { var propName = d[4].join(''); return d[0][propName]; }},
    {"name": "JSFnCall$ebnf$2", "symbols": []},
    {"name": "JSFnCall$ebnf$2", "symbols": ["JSFnCall$ebnf$2", /[a-zA-Z]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "JSFnCall", "symbols": ["JSFnCall", "_", {"literal":"."}, "_", "JSFnCall$ebnf$2", "_", "Args"], "postprocess": function(d) { var funcName = d[4].join(''); return d[0][funcName].apply(d[0], d[6]._list); }},
    {"name": "ExecCall$string$1", "symbols": [{"literal":"n"}, {"literal":"e"}, {"literal":"w"}, {"literal":" "}, {"literal":"D"}, {"literal":"a"}, {"literal":"t"}, {"literal":"e"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$1", "_", "Args"], "postprocess": function(d) { return new (Function.prototype.bind.apply(Date, [null].concat(d[2]._list))); }},
    {"name": "ExecCall$string$2", "symbols": [{"literal":"n"}, {"literal":"e"}, {"literal":"w"}, {"literal":" "}, {"literal":"D"}, {"literal":"A"}, {"literal":"T"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$2", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.DATE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$3", "symbols": [{"literal":"E"}, {"literal":"Q"}, {"literal":"U"}, {"literal":"A"}, {"literal":"L"}, {"literal":"S"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$3", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.EQUALS.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$4", "symbols": [{"literal":"N"}, {"literal":"O"}, {"literal":"T"}, {"literal":"E"}, {"literal":"Q"}, {"literal":"U"}, {"literal":"A"}, {"literal":"L"}, {"literal":"S"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$4", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.NOTEQUALS.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$5", "symbols": [{"literal":"C"}, {"literal":"O"}, {"literal":"M"}, {"literal":"P"}, {"literal":"A"}, {"literal":"R"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$5", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.COMPARE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$6", "symbols": [{"literal":"A"}, {"literal":"N"}, {"literal":"D"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$6", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.AND.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$7", "symbols": [{"literal":"O"}, {"literal":"R"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$7", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.OR.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$8", "symbols": [{"literal":"S"}, {"literal":"T"}, {"literal":"R"}, {"literal":"I"}, {"literal":"N"}, {"literal":"G"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$8", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.STRING.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$9", "symbols": [{"literal":"S"}, {"literal":"t"}, {"literal":"r"}, {"literal":"i"}, {"literal":"n"}, {"literal":"g"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$9", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.STRING.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$10", "symbols": [{"literal":"N"}, {"literal":"U"}, {"literal":"M"}, {"literal":"B"}, {"literal":"E"}, {"literal":"R"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$10", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.NUMBER.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$11", "symbols": [{"literal":"I"}, {"literal":"N"}, {"literal":"T"}, {"literal":"E"}, {"literal":"G"}, {"literal":"E"}, {"literal":"R"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$11", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.INTEGER.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$12", "symbols": [{"literal":"C"}, {"literal":"U"}, {"literal":"R"}, {"literal":"R"}, {"literal":"E"}, {"literal":"N"}, {"literal":"C"}, {"literal":"Y"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$12", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.CURRENCY.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$13", "symbols": [{"literal":"B"}, {"literal":"O"}, {"literal":"O"}, {"literal":"L"}, {"literal":"E"}, {"literal":"A"}, {"literal":"N"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$13", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.BOOLEAN.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$14", "symbols": [{"literal":"R"}, {"literal":"A"}, {"literal":"N"}, {"literal":"D"}, {"literal":"O"}, {"literal":"M"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$14", "_", "Args"], "postprocess":  function(d) { if (randomNumberGenerator) {
           return randomNumberGenerator(d[2]._list);
        }
        return EXPRESSIONS.RANDOM.apply(EXPRESSIONS, d[2]._list); } },
    {"name": "ExecCall$string$15", "symbols": [{"literal":"D"}, {"literal":"A"}, {"literal":"T"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$15", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.DATE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$16", "symbols": [{"literal":"A"}, {"literal":"R"}, {"literal":"R"}, {"literal":"A"}, {"literal":"Y"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$16", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.ARRAY.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$17", "symbols": [{"literal":"M"}, {"literal":"O"}, {"literal":"M"}, {"literal":"E"}, {"literal":"N"}, {"literal":"T"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$17", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.MOMENT.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$18", "symbols": [{"literal":"C"}, {"literal":"O"}, {"literal":"N"}, {"literal":"C"}, {"literal":"A"}, {"literal":"T"}, {"literal":"E"}, {"literal":"N"}, {"literal":"A"}, {"literal":"T"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$18", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.CONCATENATE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$19", "symbols": [{"literal":"C"}, {"literal":"A"}, {"literal":"S"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$19", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.CASE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$20", "symbols": [{"literal":"S"}, {"literal":"U"}, {"literal":"B"}, {"literal":"S"}, {"literal":"T"}, {"literal":"R"}, {"literal":"I"}, {"literal":"N"}, {"literal":"G"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$20", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.SUBSTRING.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$21", "symbols": [{"literal":"S"}, {"literal":"P"}, {"literal":"L"}, {"literal":"I"}, {"literal":"T"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$21", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.SPLIT.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$22", "symbols": [{"literal":"S"}, {"literal":"U"}, {"literal":"M"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$22", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.SUM.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$23", "symbols": [{"literal":"S"}, {"literal":"U"}, {"literal":"M"}, {"literal":"I"}, {"literal":"F"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$23", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.SUMIF.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$24", "symbols": [{"literal":"C"}, {"literal":"O"}, {"literal":"U"}, {"literal":"N"}, {"literal":"T"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$24", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.COUNT.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$25", "symbols": [{"literal":"C"}, {"literal":"O"}, {"literal":"U"}, {"literal":"N"}, {"literal":"T"}, {"literal":"I"}, {"literal":"F"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$25", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.COUNTIF.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$26", "symbols": [{"literal":"A"}, {"literal":"V"}, {"literal":"E"}, {"literal":"R"}, {"literal":"A"}, {"literal":"G"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$26", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.AVERAGE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$27", "symbols": [{"literal":"M"}, {"literal":"A"}, {"literal":"X"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$27", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.MAX.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$28", "symbols": [{"literal":"M"}, {"literal":"I"}, {"literal":"N"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$28", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.MIN.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$29", "symbols": [{"literal":"E"}, {"literal":"X"}, {"literal":"I"}, {"literal":"S"}, {"literal":"T"}, {"literal":"S"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$29", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.EXISTS.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$30", "symbols": [{"literal":"C"}, {"literal":"O"}, {"literal":"N"}, {"literal":"T"}, {"literal":"A"}, {"literal":"I"}, {"literal":"N"}, {"literal":"S"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$30", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.CONTAINS.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$31", "symbols": [{"literal":"R"}, {"literal":"O"}, {"literal":"U"}, {"literal":"N"}, {"literal":"D"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$31", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.ROUND.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$32", "symbols": [{"literal":"A"}, {"literal":"B"}, {"literal":"S"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$32", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.ABS.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$33", "symbols": [{"literal":"P"}, {"literal":"O"}, {"literal":"W"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$33", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.POW.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$34", "symbols": [{"literal":"N"}, {"literal":"O"}, {"literal":"W"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$34", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.NOW.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$35", "symbols": [{"literal":"T"}, {"literal":"O"}, {"literal":"D"}, {"literal":"A"}, {"literal":"Y"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$35", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.TODAY.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$36", "symbols": [{"literal":"A"}, {"literal":"G"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$36", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.AGE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$37", "symbols": [{"literal":"A"}, {"literal":"G"}, {"literal":"E"}, {"literal":"O"}, {"literal":"N"}], "postprocess": function joiner(d) { return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$37", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.AGEON.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$38", "symbols": [{"literal":"D"}, {"literal":"A"}, {"literal":"T"}, {"literal":"E"}, {"literal":"D"}, {"literal":"I"}, {"literal":"F"}, {"literal":"F"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$38", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.DATEDIFF.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$39", "symbols": [{"literal":"D"}, {"literal":"A"}, {"literal":"Y"}, {"literal":"O"}, {"literal":"F"}, {"literal":"M"}, {"literal":"O"}, {"literal":"N"}, {"literal":"T"}, {"literal":"H"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$39", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.DAYOFMONTH.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$40", "symbols": [{"literal":"D"}, {"literal":"A"}, {"literal":"Y"}, {"literal":"O"}, {"literal":"F"}, {"literal":"W"}, {"literal":"E"}, {"literal":"E"}, {"literal":"K"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$40", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.DAYOFWEEK.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$41", "symbols": [{"literal":"M"}, {"literal":"O"}, {"literal":"N"}, {"literal":"T"}, {"literal":"H"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$41", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.MONTH.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$42", "symbols": [{"literal":"Y"}, {"literal":"E"}, {"literal":"A"}, {"literal":"R"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$42", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.YEAR.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$43", "symbols": [{"literal":"H"}, {"literal":"O"}, {"literal":"U"}, {"literal":"R"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$43", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.HOUR.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$44", "symbols": [{"literal":"M"}, {"literal":"I"}, {"literal":"N"}, {"literal":"U"}, {"literal":"T"}, {"literal":"E"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$44", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.MINUTE.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "ExecCall$string$45", "symbols": [{"literal":"I"}, {"literal":"F"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExecCall", "symbols": ["ExecCall$string$45", "_", "Args"], "postprocess": function(d) { return EXPRESSIONS.IF.apply(EXPRESSIONS, d[2]._list); }},
    {"name": "Args", "symbols": [{"literal":"("}, "_", {"literal":")"}], "postprocess": function(d) { return {"_list":[]}; }},
    {"name": "Args", "symbols": [{"literal":"("}, "_", "ExpList", "_", {"literal":")"}], "postprocess": function(d) { return (d[2] && d[2]._list) ? d[2] : {"_list": [d[2]] }; }},
    {"name": "ArrayIndex", "symbols": ["Exp", {"literal":"["}, "_", "Exp", "_", {"literal":"]"}], "postprocess": function(d) { return d[0][d[3]]; }},
    {"name": "Exp", "symbols": ["Binop"], "postprocess": id},
    {"name": "Binop", "symbols": ["ExpOr"], "postprocess": id},
    {"name": "Array", "symbols": [{"literal":"["}, "_", {"literal":"]"}], "postprocess": function(d) { return []; }},
    {"name": "Array$ebnf$1", "symbols": []},
    {"name": "Array$ebnf$1$subexpression$1", "symbols": ["_", {"literal":","}, "_", "Exp"]},
    {"name": "Array$ebnf$1", "symbols": ["Array$ebnf$1", "Array$ebnf$1$subexpression$1"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "Array", "symbols": [{"literal":"["}, "_", "Exp", "Array$ebnf$1", "_", {"literal":"]"}], "postprocess":  function(d) {
            let output = [d[2]];
            for (let i in d[3]) {
                output.push(d[3][i][3]);
            }
            return output;
        }
                                                    },
    {"name": "Parenthesized", "symbols": [{"literal":"("}, "_", "Exp", "_", {"literal":")"}], "postprocess": function(d) { return d[2]; }},
    {"name": "ExpOr$string$1", "symbols": [{"literal":"|"}, {"literal":"|"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpOr", "symbols": ["ExpOr", "_", "ExpOr$string$1", "_", "ExpAnd"], "postprocess": function(d) { return EXPRESSIONS.OR(d[0], d[4]); }},
    {"name": "ExpOr", "symbols": ["ExpAnd"], "postprocess": id},
    {"name": "ExpAnd$string$1", "symbols": [{"literal":"&"}, {"literal":"&"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpAnd", "symbols": ["ExpAnd", "_", "ExpAnd$string$1", "_", "ExpComparison"], "postprocess": function(d) { return  EXPRESSIONS.AND(d[0], d[4]); }},
    {"name": "ExpAnd", "symbols": ["ExpComparison"], "postprocess": id},
    {"name": "ExpAnd", "symbols": ["DeferedExpComparison"], "postprocess": id},
    {"name": "DeferedExpComparison", "symbols": [{"literal":"<"}, "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.COMPARE('<', ele, d[2]); }  }},
    {"name": "DeferedExpComparison", "symbols": [{"literal":">"}, "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.COMPARE('>', ele, d[2]); }  }},
    {"name": "DeferedExpComparison$string$1", "symbols": [{"literal":"<"}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "DeferedExpComparison", "symbols": ["DeferedExpComparison$string$1", "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.COMPARE('<=', ele, d[2]); }  }},
    {"name": "DeferedExpComparison$string$2", "symbols": [{"literal":">"}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "DeferedExpComparison", "symbols": ["DeferedExpComparison$string$2", "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.COMPARE('>=', ele, d[2]); }  }},
    {"name": "DeferedExpComparison$string$3", "symbols": [{"literal":"="}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "DeferedExpComparison", "symbols": ["DeferedExpComparison$string$3", "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.EQUALS(ele, d[2]); }  }},
    {"name": "DeferedExpComparison$string$4", "symbols": [{"literal":"!"}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "DeferedExpComparison", "symbols": ["DeferedExpComparison$string$4", "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.NOTEQUALS(ele, d[2]); }  }},
    {"name": "DeferedExpComparison$string$5", "symbols": [{"literal":"<"}, {"literal":">"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "DeferedExpComparison", "symbols": ["DeferedExpComparison$string$5", "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.NOTEQUALS(ele, d[2]); }  }},
    {"name": "DeferedExpComparison", "symbols": [{"literal":"="}, "_", "ExpSum"], "postprocess": function(d) { return function(ele) { return EXPRESSIONS.EQUALS(ele, d[2]); }  }},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", {"literal":"<"}, "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.COMPARE('<', d[0], d[4]); }},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", {"literal":">"}, "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.COMPARE('>', d[0], d[4]); }},
    {"name": "ExpComparison$string$1", "symbols": [{"literal":"<"}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", "ExpComparison$string$1", "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.COMPARE('<=', d[0], d[4]); }},
    {"name": "ExpComparison$string$2", "symbols": [{"literal":">"}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", "ExpComparison$string$2", "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.COMPARE('>=', d[0], d[4]); }},
    {"name": "ExpComparison$string$3", "symbols": [{"literal":"="}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", "ExpComparison$string$3", "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.EQUALS(d[0], d[4]);}},
    {"name": "ExpComparison$string$4", "symbols": [{"literal":"!"}, {"literal":"="}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", "ExpComparison$string$4", "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.NOTEQUALS(d[0], d[4]);}},
    {"name": "ExpComparison$string$5", "symbols": [{"literal":"<"}, {"literal":">"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", "ExpComparison$string$5", "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.NOTEQUALS(d[0], d[4]);}},
    {"name": "ExpComparison", "symbols": ["ExpComparison", "_", {"literal":"="}, "_", "ExpSum"], "postprocess": function(d) { return EXPRESSIONS.EQUALS(d[0], d[4]);}},
    {"name": "ExpComparison", "symbols": ["ExpSum"], "postprocess": id},
    {"name": "ExpSum", "symbols": ["ExpSum", "_", {"literal":"+"}, "_", "ExpProduct"], "postprocess": function(d) { return d[0] + d[4]; }},
    {"name": "ExpSum", "symbols": ["ExpSum", "_", {"literal":"-"}, "_", "ExpProduct"], "postprocess": function(d) { return d[0] - d[4]; }},
    {"name": "ExpSum", "symbols": ["ExpProduct"], "postprocess": id},
    {"name": "ExpProduct", "symbols": ["ExpProduct", "_", {"literal":"*"}, "_", "ExpUnary"], "postprocess": function(d) { return d[0] * d[4]; }},
    {"name": "ExpProduct", "symbols": ["ExpProduct", "_", {"literal":"/"}, "_", "ExpUnary"], "postprocess": function(d) { return d[0] / d[4]; }},
    {"name": "ExpProduct", "symbols": ["ExpProduct", "_", {"literal":"%"}, "_", "ExpUnary"], "postprocess": function(d) { return d[0] % d[4]; }},
    {"name": "ExpProduct", "symbols": ["ExpUnary"], "postprocess": id},
    {"name": "ExpUnary", "symbols": [{"literal":"!"}, "_", "ExpPow"], "postprocess": function(d) { return ! d[2];}},
    {"name": "ExpUnary", "symbols": ["ExpPow"], "postprocess": id},
    {"name": "ExpPow", "symbols": ["Atom"], "postprocess": id},
    {"name": "ExpPow", "symbols": ["Atom", "_", {"literal":"^"}, "_", "ExpPow"], "postprocess": function(d) { return  EXPRESSIONS.POW(d[0], d[4]); }},
    {"name": "Atom", "symbols": ["NULL"], "postprocess": id},
    {"name": "Atom", "symbols": ["B"], "postprocess": id},
    {"name": "Atom", "symbols": ["Number"], "postprocess": id},
    {"name": "Atom", "symbols": ["Token"], "postprocess": id},
    {"name": "Atom", "symbols": ["String"], "postprocess": id},
    {"name": "Atom", "symbols": ["Parenthesized"], "postprocess": id},
    {"name": "Atom", "symbols": ["Array"], "postprocess": id},
    {"name": "Atom", "symbols": ["JSFnCall"], "postprocess": id},
    {"name": "NULL$string$1", "symbols": [{"literal":"N"}, {"literal":"U"}, {"literal":"L"}, {"literal":"L"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "NULL", "symbols": ["NULL$string$1"], "postprocess": function(d) { return null; }},
    {"name": "NULL$string$2", "symbols": [{"literal":"n"}, {"literal":"u"}, {"literal":"l"}, {"literal":"l"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "NULL", "symbols": ["NULL$string$2"], "postprocess": function(d) { return null; }},
    {"name": "NULL$string$3", "symbols": [{"literal":"u"}, {"literal":"n"}, {"literal":"d"}, {"literal":"e"}, {"literal":"f"}, {"literal":"i"}, {"literal":"n"}, {"literal":"e"}, {"literal":"d"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "NULL", "symbols": ["NULL$string$3"], "postprocess": function(d) { return undefined; }},
    {"name": "B$string$1", "symbols": [{"literal":"t"}, {"literal":"r"}, {"literal":"u"}, {"literal":"e"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "B", "symbols": ["B$string$1"], "postprocess": function(d) { return true; }},
    {"name": "B$string$2", "symbols": [{"literal":"f"}, {"literal":"a"}, {"literal":"l"}, {"literal":"s"}, {"literal":"e"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "B", "symbols": ["B$string$2"], "postprocess": function(d) { return false; }},
    {"name": "B$subexpression$1", "symbols": [/[uU]/, /[pP]/, /[pP]/, /[eE]/, /[rR]/], "postprocess": function(d) {return d.join(""); }},
    {"name": "B", "symbols": ["B$subexpression$1"], "postprocess": function(d) { return EXPRESSIONS.UPPER; }},
    {"name": "B$subexpression$2", "symbols": [/[lL]/, /[oO]/, /[wW]/, /[eE]/, /[rR]/], "postprocess": function(d) {return d.join(""); }},
    {"name": "B", "symbols": ["B$subexpression$2"], "postprocess": function(d) { return EXPRESSIONS.LOWER; }},
    {"name": "B$subexpression$3", "symbols": [/[sS]/, /[eE]/, /[nN]/, /[tT]/, /[eE]/, /[nN]/, /[cC]/, /[eE]/], "postprocess": function(d) {return d.join(""); }},
    {"name": "B", "symbols": ["B$subexpression$3"], "postprocess": function(d) { return EXPRESSIONS.SENTENCE; }},
    {"name": "B$subexpression$4", "symbols": [/[tT]/, /[iI]/, /[tT]/, /[lL]/, /[eE]/], "postprocess": function(d) {return d.join(""); }},
    {"name": "B", "symbols": ["B$subexpression$4"], "postprocess": function(d) { return EXPRESSIONS.TITLE; }},
    {"name": "unsigned_int$ebnf$1", "symbols": [/[0-9]/]},
    {"name": "unsigned_int$ebnf$1", "symbols": ["unsigned_int$ebnf$1", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "unsigned_int", "symbols": ["unsigned_int$ebnf$1"], "postprocess": 
        function(d) {
            return parseInt(d[0].join(""));
        }
        },
    {"name": "int$ebnf$1$subexpression$1", "symbols": [{"literal":"-"}]},
    {"name": "int$ebnf$1$subexpression$1", "symbols": [{"literal":"+"}]},
    {"name": "int$ebnf$1", "symbols": ["int$ebnf$1$subexpression$1"], "postprocess": id},
    {"name": "int$ebnf$1", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "int$ebnf$2", "symbols": [/[0-9]/]},
    {"name": "int$ebnf$2", "symbols": ["int$ebnf$2", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "int", "symbols": ["int$ebnf$1", "int$ebnf$2"], "postprocess": 
        function(d) {
            if (d[0]) {
                return parseInt(d[0][0]+d[1].join(""));
            } else {
                return parseInt(d[1].join(""));
            }
        }
        },
    {"name": "unsigned_decimal$ebnf$1", "symbols": [/[0-9]/]},
    {"name": "unsigned_decimal$ebnf$1", "symbols": ["unsigned_decimal$ebnf$1", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "unsigned_decimal$ebnf$2$subexpression$1$ebnf$1", "symbols": [/[0-9]/]},
    {"name": "unsigned_decimal$ebnf$2$subexpression$1$ebnf$1", "symbols": ["unsigned_decimal$ebnf$2$subexpression$1$ebnf$1", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "unsigned_decimal$ebnf$2$subexpression$1", "symbols": [{"literal":"."}, "unsigned_decimal$ebnf$2$subexpression$1$ebnf$1"]},
    {"name": "unsigned_decimal$ebnf$2", "symbols": ["unsigned_decimal$ebnf$2$subexpression$1"], "postprocess": id},
    {"name": "unsigned_decimal$ebnf$2", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "unsigned_decimal", "symbols": ["unsigned_decimal$ebnf$1", "unsigned_decimal$ebnf$2"], "postprocess": 
        function(d) {
            return parseFloat(
                d[0].join("") +
                (d[1] ? "."+d[1][1].join("") : "")
            );
        }
        },
    {"name": "decimal$ebnf$1", "symbols": [{"literal":"-"}], "postprocess": id},
    {"name": "decimal$ebnf$1", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "decimal$ebnf$2", "symbols": [/[0-9]/]},
    {"name": "decimal$ebnf$2", "symbols": ["decimal$ebnf$2", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "decimal$ebnf$3$subexpression$1$ebnf$1", "symbols": [/[0-9]/]},
    {"name": "decimal$ebnf$3$subexpression$1$ebnf$1", "symbols": ["decimal$ebnf$3$subexpression$1$ebnf$1", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "decimal$ebnf$3$subexpression$1", "symbols": [{"literal":"."}, "decimal$ebnf$3$subexpression$1$ebnf$1"]},
    {"name": "decimal$ebnf$3", "symbols": ["decimal$ebnf$3$subexpression$1"], "postprocess": id},
    {"name": "decimal$ebnf$3", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "decimal", "symbols": ["decimal$ebnf$1", "decimal$ebnf$2", "decimal$ebnf$3"], "postprocess": 
        function(d) {
            return parseFloat(
                (d[0] || "") +
                d[1].join("") +
                (d[2] ? "."+d[2][1].join("") : "")
            );
        }
        },
    {"name": "percentage", "symbols": ["decimal", {"literal":"%"}], "postprocess": 
        function(d) {
            return d[0]/100;
        }
        },
    {"name": "jsonfloat$ebnf$1", "symbols": [{"literal":"-"}], "postprocess": id},
    {"name": "jsonfloat$ebnf$1", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "jsonfloat$ebnf$2", "symbols": [/[0-9]/]},
    {"name": "jsonfloat$ebnf$2", "symbols": ["jsonfloat$ebnf$2", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "jsonfloat$ebnf$3$subexpression$1$ebnf$1", "symbols": [/[0-9]/]},
    {"name": "jsonfloat$ebnf$3$subexpression$1$ebnf$1", "symbols": ["jsonfloat$ebnf$3$subexpression$1$ebnf$1", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "jsonfloat$ebnf$3$subexpression$1", "symbols": [{"literal":"."}, "jsonfloat$ebnf$3$subexpression$1$ebnf$1"]},
    {"name": "jsonfloat$ebnf$3", "symbols": ["jsonfloat$ebnf$3$subexpression$1"], "postprocess": id},
    {"name": "jsonfloat$ebnf$3", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "jsonfloat$ebnf$4$subexpression$1$ebnf$1", "symbols": [/[+-]/], "postprocess": id},
    {"name": "jsonfloat$ebnf$4$subexpression$1$ebnf$1", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "jsonfloat$ebnf$4$subexpression$1$ebnf$2", "symbols": [/[0-9]/]},
    {"name": "jsonfloat$ebnf$4$subexpression$1$ebnf$2", "symbols": ["jsonfloat$ebnf$4$subexpression$1$ebnf$2", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "jsonfloat$ebnf$4$subexpression$1", "symbols": [/[eE]/, "jsonfloat$ebnf$4$subexpression$1$ebnf$1", "jsonfloat$ebnf$4$subexpression$1$ebnf$2"]},
    {"name": "jsonfloat$ebnf$4", "symbols": ["jsonfloat$ebnf$4$subexpression$1"], "postprocess": id},
    {"name": "jsonfloat$ebnf$4", "symbols": [], "postprocess": function(d) {return null;}},
    {"name": "jsonfloat", "symbols": ["jsonfloat$ebnf$1", "jsonfloat$ebnf$2", "jsonfloat$ebnf$3", "jsonfloat$ebnf$4"], "postprocess": 
        function(d) {
            return parseFloat(
                (d[0] || "") +
                d[1].join("") +
                (d[2] ? "."+d[2][1].join("") : "") +
                (d[3] ? "e" + (d[3][1] || "+") + d[3][2].join("") : "")
            );
        }
        },
    {"name": "Number$ebnf$1", "symbols": []},
    {"name": "Number$ebnf$1", "symbols": ["Number$ebnf$1", /[0-9]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "Number", "symbols": [{"literal":"."}, "Number$ebnf$1"], "postprocess": function(d) { return parseFloat('0.' + d[1].join(""), 10); }},
    {"name": "Number", "symbols": ["jsonfloat"], "postprocess": id},
    {"name": "dqstring$ebnf$1", "symbols": []},
    {"name": "dqstring$ebnf$1", "symbols": ["dqstring$ebnf$1", "dstrchar"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "dqstring", "symbols": [{"literal":"\""}, "dqstring$ebnf$1", {"literal":"\""}], "postprocess": function(d) {return d[1].join(""); }},
    {"name": "sqstring$ebnf$1", "symbols": []},
    {"name": "sqstring$ebnf$1", "symbols": ["sqstring$ebnf$1", "sstrchar"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "sqstring", "symbols": [{"literal":"'"}, "sqstring$ebnf$1", {"literal":"'"}], "postprocess": function(d) {return d[1].join(""); }},
    {"name": "btstring$ebnf$1", "symbols": []},
    {"name": "btstring$ebnf$1", "symbols": ["btstring$ebnf$1", /[^`]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "btstring", "symbols": [{"literal":"`"}, "btstring$ebnf$1", {"literal":"`"}], "postprocess": function(d) {return d[1].join(""); }},
    {"name": "dstrchar", "symbols": [/[^\\"\n]/], "postprocess": id},
    {"name": "dstrchar", "symbols": [{"literal":"\\"}, "strescape"], "postprocess": 
        function(d) {
            return JSON.parse("\""+d.join("")+"\"");
        }
        },
    {"name": "sstrchar", "symbols": [/[^\\'\n]/], "postprocess": id},
    {"name": "sstrchar", "symbols": [{"literal":"\\"}, "strescape"], "postprocess": function(d) { return JSON.parse("\""+d.join("")+"\""); }},
    {"name": "sstrchar$string$1", "symbols": [{"literal":"\\"}, {"literal":"'"}], "postprocess": function joiner(d) {return d.join('');}},
    {"name": "sstrchar", "symbols": ["sstrchar$string$1"], "postprocess": function(d) {return "'"; }},
    {"name": "strescape", "symbols": [/["\\\/bfnrt]/], "postprocess": id},
    {"name": "strescape", "symbols": [{"literal":"u"}, /[a-fA-F0-9]/, /[a-fA-F0-9]/, /[a-fA-F0-9]/, /[a-fA-F0-9]/], "postprocess": 
        function(d) {
            return d.join("");
        }
        },
    {"name": "String$ebnf$1", "symbols": []},
    {"name": "String$ebnf$1", "symbols": ["String$ebnf$1", "dstrchar"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "String", "symbols": [{"literal":"\""}, "String$ebnf$1", {"literal":"\""}], "postprocess": function(d) { return parseTokensInString(d[1].join("")); }},
    {"name": "String$ebnf$2", "symbols": []},
    {"name": "String$ebnf$2", "symbols": ["String$ebnf$2", "sstrchar"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "String", "symbols": [{"literal":"'"}, "String$ebnf$2", {"literal":"'"}], "postprocess": function(d) { return parseTokensInString(d[1].join("")); }},
    {"name": "Token$ebnf$1", "symbols": []},
    {"name": "Token$ebnf$1", "symbols": ["Token$ebnf$1", /[a-zA-Z0-9|:\/\(\)\-\?_\. ]/], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "Token", "symbols": [{"literal":"%"}, "Token$ebnf$1", {"literal":"%"}], "postprocess": function(d) { return resolveToken(d[1].join("")); }},
    {"name": "_$ebnf$1", "symbols": []},
    {"name": "_$ebnf$1", "symbols": ["_$ebnf$1", "wschar"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "_", "symbols": ["_$ebnf$1"], "postprocess": function(d) {return null;}},
    {"name": "__$ebnf$1", "symbols": ["wschar"]},
    {"name": "__$ebnf$1", "symbols": ["__$ebnf$1", "wschar"], "postprocess": function arrpush(d) {return d[0].concat([d[1]]);}},
    {"name": "__", "symbols": ["__$ebnf$1"], "postprocess": function(d) {return null;}},
    {"name": "wschar", "symbols": [/[ \t\n\v\f]/], "postprocess": id}
];
let ParserStart = "main";


/************************** Day js **************************/

var SECONDS_A_MINUTE = 60;
var SECONDS_A_HOUR = SECONDS_A_MINUTE * 60;
var SECONDS_A_DAY = SECONDS_A_HOUR * 24;
var SECONDS_A_WEEK = SECONDS_A_DAY * 7;
var MILLISECONDS_A_SECOND = 1e3;
var MILLISECONDS_A_MINUTE = SECONDS_A_MINUTE * MILLISECONDS_A_SECOND;
var MILLISECONDS_A_HOUR = SECONDS_A_HOUR * MILLISECONDS_A_SECOND;
var MILLISECONDS_A_DAY = SECONDS_A_DAY * MILLISECONDS_A_SECOND;
var MILLISECONDS_A_WEEK = SECONDS_A_WEEK * MILLISECONDS_A_SECOND; // English locales

var MS = 'millisecond';
var S = 'second';
var MIN = 'minute';
var H = 'hour';
var D = 'day';
var W = 'week';
var M = 'month';
var Q = 'quarter';
var Y = 'year';
var DATE = 'date';
var FORMAT_DEFAULT = 'YYYY-MM-DDTHH:mm:ssZ';
var INVALID_DATE_STRING = 'Invalid Date'; // regex

var REGEX_PARSE = /^(\d{4})-?(\d{1,2})-?(\d{0,2})[^0-9]*(\d{1,2})?:?(\d{1,2})?:?(\d{1,2})?.?(\d{1,3})?$/;
var REGEX_FORMAT = /\[([^\]]+)]|Y{2,4}|M{1,4}|D{1,2}|d{1,4}|H{1,2}|h{1,2}|a|A|m{1,2}|s{1,2}|Z{1,2}|SSS/g;

var padStart = function padStart(string, length, pad) {
  var s = String(string);
  if (!s || s.length >= length) return string;
  return "" + Array(length + 1 - s.length).join(pad) + string;
};

var padZoneStr = function padZoneStr(instance) {
  var negMinuts = -instance.utcOffset();
  var minutes = Math.abs(negMinuts);
  var hourOffset = Math.floor(minutes / 60);
  var minuteOffset = minutes % 60;
  return "" + (negMinuts <= 0 ? '+' : '-') + padStart(hourOffset, 2, '0') + ":" + padStart(minuteOffset, 2, '0');
};

var monthDiff = function monthDiff(a, b) {
  // function from moment.js in order to keep the same result
  var wholeMonthDiff = (b.year() - a.year()) * 12 + (b.month() - a.month());
  var anchor = a.clone().add(wholeMonthDiff, M);
  var c = b - anchor < 0;
  var anchor2 = a.clone().add(wholeMonthDiff + (c ? -1 : 1), M);
  return Number(-(wholeMonthDiff + (b - anchor) / (c ? anchor - anchor2 : anchor2 - anchor)) || 0);
};

var absFloor = function absFloor(n) {
  return n < 0 ? Math.ceil(n) || 0 : Math.floor(n);
};

var prettyUnit = function prettyUnit(u) {
  var special = {
    M: M,
    y: Y,
    w: W,
    d: D,
    h: H,
    m: MIN,
    s: S,
    ms: MS,
    Q: Q
  };
  return special[u] || String(u || '').toLowerCase().replace(/s$/, '');
};

var isUndefined = function isUndefined(s) {
  return s === undefined;
};

var U = {
  s: padStart,
  z: padZoneStr,
  m: monthDiff,
  a: absFloor,
  p: prettyUnit,
  u: isUndefined
};

// We don't need weekdaysShort, weekdaysMin, monthsShort in en.js locale
var en = {
  name: 'en',
  weekdays: 'Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday'.split('_'),
  months: 'January_February_March_April_May_June_July_August_September_October_November_December'.split('_')
};

var L = 'en'; // global locale

var Ls = {}; // global loaded locale

Ls[L] = en;

var isDayjs = function isDayjs(d) {
  return d instanceof Dayjs;
}; // eslint-disable-line no-use-before-define


var parseLocale = function parseLocale(preset, object, isLocal) {
  var l;
  if (!preset) return L;

  if (typeof preset === 'string') {
    if (Ls[preset]) {
      l = preset;
    }

    if (object) {
      Ls[preset] = object;
      l = preset;
    }
  } else {
    var name = preset.name;
    Ls[name] = preset;
    l = name;
  }

  if (!isLocal) L = l;
  return l;
};

var dayjs = function dayjs(date, c, pl) {
  if (isDayjs(date)) {
    return date.clone();
  } // eslint-disable-next-line no-nested-ternary


  var cfg = c ? typeof c === 'string' ? {
    format: c,
    pl: pl
  } : c : {};
  cfg.date = date;
  return new Dayjs(cfg); // eslint-disable-line no-use-before-define
};

var wrapper = function wrapper(date, instance) {
  return dayjs(date, {
    locale: instance.$L,
    utc: instance.$u
  });
};

var Utils = U; // for plugin use

Utils.l = parseLocale;
Utils.i = isDayjs;
Utils.w = wrapper;

var parseDate = function parseDate(cfg) {
  var date = cfg.date,
      utc = cfg.utc;
  if (date === null) return new Date(NaN); // null is invalid

  if (Utils.u(date)) return new Date(); // today

  if (date instanceof Date) return new Date(date);

  if (typeof date === 'string' && !/Z$/i.test(date)) {
    var d = date.match(REGEX_PARSE);

    if (d) {
      if (utc) {
        return new Date(Date.UTC(d[1], d[2] - 1, d[3] || 1, d[4] || 0, d[5] || 0, d[6] || 0, d[7] || 0));
      }

      return new Date(d[1], d[2] - 1, d[3] || 1, d[4] || 0, d[5] || 0, d[6] || 0, d[7] || 0);
    }
  }

  return new Date(date); // everything else
};

var Dayjs =
/*#__PURE__*/
function () {
  function Dayjs(cfg) {
    this.$L = this.$L || parseLocale(cfg.locale, null, true);
    this.parse(cfg); // for plugin
  }

  var _proto = Dayjs.prototype;

  _proto.parse = function parse(cfg) {
    this.$d = parseDate(cfg);
    this.init();
  };

  _proto.init = function init() {
    var $d = this.$d;
    this.$y = $d.getFullYear();
    this.$M = $d.getMonth();
    this.$D = $d.getDate();
    this.$W = $d.getDay();
    this.$H = $d.getHours();
    this.$m = $d.getMinutes();
    this.$s = $d.getSeconds();
    this.$ms = $d.getMilliseconds();
  } // eslint-disable-next-line class-methods-use-this
  ;

  _proto.$utils = function $utils() {
    return Utils;
  };

  _proto.isValid = function isValid() {
    return !(this.$d.toString() === INVALID_DATE_STRING);
  };

  _proto.isSame = function isSame(that, units) {
    var other = dayjs(that);
    return this.startOf(units) <= other && other <= this.endOf(units);
  };

  _proto.isAfter = function isAfter(that, units) {
    return dayjs(that) < this.startOf(units);
  };

  _proto.isBefore = function isBefore(that, units) {
    return this.endOf(units) < dayjs(that);
  };

  _proto.$g = function $g(input, get, set) {
    if (Utils.u(input)) return this[get];
    return this.set(set, input);
  };

  _proto.year = function year(input) {
    return this.$g(input, '$y', Y);
  };

  _proto.month = function month(input) {
    return this.$g(input, '$M', M);
  };

  _proto.day = function day(input) {
    return this.$g(input, '$W', D);
  };

  _proto.date = function date(input) {
    return this.$g(input, '$D', DATE);
  };

  _proto.hour = function hour(input) {
    return this.$g(input, '$H', H);
  };

  _proto.minute = function minute(input) {
    return this.$g(input, '$m', MIN);
  };

  _proto.second = function second(input) {
    return this.$g(input, '$s', S);
  };

  _proto.millisecond = function millisecond(input) {
    return this.$g(input, '$ms', MS);
  };

  _proto.unix = function unix() {
    return Math.floor(this.valueOf() / 1000);
  };

  _proto.valueOf = function valueOf() {
    // timezone(hour) * 60 * 60 * 1000 => ms
    return this.$d.getTime();
  };

  _proto.startOf = function startOf(units, _startOf) {
    var _this = this;

    // startOf -> endOf
    var isStartOf = !Utils.u(_startOf) ? _startOf : true;
    var unit = Utils.p(units);

    var instanceFactory = function instanceFactory(d, m) {
      var ins = Utils.w(_this.$u ? Date.UTC(_this.$y, m, d) : new Date(_this.$y, m, d), _this);
      return isStartOf ? ins : ins.endOf(D);
    };

    var instanceFactorySet = function instanceFactorySet(method, slice) {
      var argumentStart = [0, 0, 0, 0];
      var argumentEnd = [23, 59, 59, 999];
      return Utils.w(_this.toDate()[method].apply( // eslint-disable-line prefer-spread
      _this.toDate(), (isStartOf ? argumentStart : argumentEnd).slice(slice)), _this);
    };

    var $W = this.$W,
        $M = this.$M,
        $D = this.$D;
    var utcPad = "set" + (this.$u ? 'UTC' : '');

    switch (unit) {
      case Y:
        return isStartOf ? instanceFactory(1, 0) : instanceFactory(31, 11);

      case M:
        return isStartOf ? instanceFactory(1, $M) : instanceFactory(0, $M + 1);

      case W:
        {
          var weekStart = this.$locale().weekStart || 0;
          var gap = ($W < weekStart ? $W + 7 : $W) - weekStart;
          return instanceFactory(isStartOf ? $D - gap : $D + (6 - gap), $M);
        }

      case D:
      case DATE:
        return instanceFactorySet(utcPad + "Hours", 0);

      case H:
        return instanceFactorySet(utcPad + "Minutes", 1);

      case MIN:
        return instanceFactorySet(utcPad + "Seconds", 2);

      case S:
        return instanceFactorySet(utcPad + "Milliseconds", 3);

      default:
        return this.clone();
    }
  };

  _proto.endOf = function endOf(arg) {
    return this.startOf(arg, false);
  };

  _proto.$set = function $set(units, _int) {
    var _C$D$C$DATE$C$M$C$Y$C;

    // private set
    var unit = Utils.p(units);
    var utcPad = "set" + (this.$u ? 'UTC' : '');
    var name = (_C$D$C$DATE$C$M$C$Y$C = {}, _C$D$C$DATE$C$M$C$Y$C[D] = utcPad + "Date", _C$D$C$DATE$C$M$C$Y$C[DATE] = utcPad + "Date", _C$D$C$DATE$C$M$C$Y$C[M] = utcPad + "Month", _C$D$C$DATE$C$M$C$Y$C[Y] = utcPad + "FullYear", _C$D$C$DATE$C$M$C$Y$C[H] = utcPad + "Hours", _C$D$C$DATE$C$M$C$Y$C[MIN] = utcPad + "Minutes", _C$D$C$DATE$C$M$C$Y$C[S] = utcPad + "Seconds", _C$D$C$DATE$C$M$C$Y$C[MS] = utcPad + "Milliseconds", _C$D$C$DATE$C$M$C$Y$C)[unit];
    var arg = unit === D ? this.$D + (_int - this.$W) : _int;

    if (unit === M || unit === Y) {
      // clone is for badMutable plugin
      var date = this.clone().set(DATE, 1);
      date.$d[name](arg);
      date.init();
      this.$d = date.set(DATE, Math.min(this.$D, date.daysInMonth())).toDate();
    } else if (name) this.$d[name](arg);

    this.init();
    return this;
  };

  _proto.set = function set(string, _int2) {
    return this.clone().$set(string, _int2);
  };

  _proto.get = function get(unit) {
    return this[Utils.p(unit)]();
  };

  _proto.add = function add(number, units) {
    var _this2 = this,
        _C$MIN$C$H$C$S$unit;

    number = Number(number); // eslint-disable-line no-param-reassign

    var unit = Utils.p(units);

    var instanceFactorySet = function instanceFactorySet(n) {
      var d = dayjs(_this2);
      return Utils.w(d.date(d.date() + Math.round(n * number)), _this2);
    };

    if (unit === M) {
      return this.set(M, this.$M + number);
    }

    if (unit === Y) {
      return this.set(Y, this.$y + number);
    }

    if (unit === D) {
      return instanceFactorySet(1);
    }

    if (unit === W) {
      return instanceFactorySet(7);
    }

    var step = (_C$MIN$C$H$C$S$unit = {}, _C$MIN$C$H$C$S$unit[MIN] = MILLISECONDS_A_MINUTE, _C$MIN$C$H$C$S$unit[H] = MILLISECONDS_A_HOUR, _C$MIN$C$H$C$S$unit[S] = MILLISECONDS_A_SECOND, _C$MIN$C$H$C$S$unit)[unit] || 1; // ms

    var nextTimeStamp = this.valueOf() + number * step;
    return Utils.w(nextTimeStamp, this);
  };

  _proto.subtract = function subtract(number, string) {
    return this.add(number * -1, string);
  };

  _proto.format = function format(formatStr) {
    var _this3 = this;

    if (!this.isValid()) return INVALID_DATE_STRING;
    var str = formatStr || FORMAT_DEFAULT;
    var zoneStr = Utils.z(this);
    var locale = this.$locale();
    var $H = this.$H,
        $m = this.$m,
        $M = this.$M;
    var weekdays = locale.weekdays,
        months = locale.months,
        meridiem = locale.meridiem;

    var getShort = function getShort(arr, index, full, length) {
      return arr && (arr[index] || arr(_this3, str)) || full[index].substr(0, length);
    };

    var get$H = function get$H(num) {
      return Utils.s($H % 12 || 12, num, '0');
    };

    var meridiemFunc = meridiem || function (hour, minute, isLowercase) {
      var m = hour < 12 ? 'AM' : 'PM';
      return isLowercase ? m.toLowerCase() : m;
    };

    var matches = {
      YY: String(this.$y).slice(-2),
      YYYY: this.$y,
      M: $M + 1,
      MM: Utils.s($M + 1, 2, '0'),
      MMM: getShort(locale.monthsShort, $M, months, 3),
      MMMM: months[$M] || months(this, str),
      D: this.$D,
      DD: Utils.s(this.$D, 2, '0'),
      d: String(this.$W),
      dd: getShort(locale.weekdaysMin, this.$W, weekdays, 2),
      ddd: getShort(locale.weekdaysShort, this.$W, weekdays, 3),
      dddd: weekdays[this.$W],
      H: String($H),
      HH: Utils.s($H, 2, '0'),
      h: get$H(1),
      hh: get$H(2),
      a: meridiemFunc($H, $m, true),
      A: meridiemFunc($H, $m, false),
      m: String($m),
      mm: Utils.s($m, 2, '0'),
      s: String(this.$s),
      ss: Utils.s(this.$s, 2, '0'),
      SSS: Utils.s(this.$ms, 3, '0'),
      Z: zoneStr // 'ZZ' logic below

    };
    return str.replace(REGEX_FORMAT, function (match, $1) {
      return $1 || matches[match] || zoneStr.replace(':', '');
    }); // 'ZZ'
  };

  _proto.utcOffset = function utcOffset() {
    // Because a bug at FF24, we're rounding the timezone offset around 15 minutes
    // https://github.com/moment/moment/pull/1871
    return -Math.round(this.$d.getTimezoneOffset() / 15) * 15;
  };

  _proto.diff = function diff(input, units, _float) {
    var _C$Y$C$M$C$Q$C$W$C$D$;

    var unit = Utils.p(units);
    var that = dayjs(input);
    var zoneDelta = (that.utcOffset() - this.utcOffset()) * MILLISECONDS_A_MINUTE;
    var diff = this - that;
    var result = Utils.m(this, that);
    result = (_C$Y$C$M$C$Q$C$W$C$D$ = {}, _C$Y$C$M$C$Q$C$W$C$D$[Y] = result / 12, _C$Y$C$M$C$Q$C$W$C$D$[M] = result, _C$Y$C$M$C$Q$C$W$C$D$[Q] = result / 3, _C$Y$C$M$C$Q$C$W$C$D$[W] = (diff - zoneDelta) / MILLISECONDS_A_WEEK, _C$Y$C$M$C$Q$C$W$C$D$[D] = (diff - zoneDelta) / MILLISECONDS_A_DAY, _C$Y$C$M$C$Q$C$W$C$D$[H] = diff / MILLISECONDS_A_HOUR, _C$Y$C$M$C$Q$C$W$C$D$[MIN] = diff / MILLISECONDS_A_MINUTE, _C$Y$C$M$C$Q$C$W$C$D$[S] = diff / MILLISECONDS_A_SECOND, _C$Y$C$M$C$Q$C$W$C$D$)[unit] || diff; // milliseconds

    return _float ? result : Utils.a(result);
  };

  _proto.daysInMonth = function daysInMonth() {
    return this.endOf(M).$D;
  };

  _proto.$locale = function $locale() {
    // get locale object
    return Ls[this.$L];
  };

  _proto.locale = function locale(preset, object) {
    if (!preset) return this.$L;
    var that = this.clone();
    that.$L = parseLocale(preset, object, true);
    return that;
  };

  _proto.clone = function clone() {
    return Utils.w(this.toDate(), this);
  };

  _proto.toDate = function toDate() {
    return new Date(this.$d);
  };

  _proto.toJSON = function toJSON() {
    return this.isValid() ? this.toISOString() : null;
  };

  _proto.toISOString = function toISOString() {
    // ie 8 return
    // new Dayjs(this.valueOf() + this.$d.getTimezoneOffset() * 60000)
    // .format('YYYY-MM-DDTHH:mm:ss.SSS[Z]')
    return this.$d.toISOString();
  };

  _proto.toString = function toString() {
    return this.$d.toUTCString();
  };

  return Dayjs;
}();

dayjs.prototype = Dayjs.prototype;

dayjs.extend = function (plugin, option) {
  plugin(option, Dayjs, dayjs);
  return dayjs;
};

dayjs.locale = parseLocale;
dayjs.isDayjs = isDayjs;

dayjs.unix = function (timestamp) {
  return dayjs(timestamp * 1e3);
};

dayjs.en = Ls[L];
dayjs.Ls = Ls;

var advancedFormat = (function (o, c, d) {
  // locale needed later
  var proto = c.prototype;
  var oldFormat = proto.format;

  d.en.ordinal = function (number) {
    var s = ['th', 'st', 'nd', 'rd'];
    var v = number % 100;
    return "[" + number + (s[(v - 20) % 10] || s[v] || s[0]) + "]";
  }; // extend en locale here


  proto.format = function (formatStr) {
    var _this = this;

    var locale = this.$locale();
    var utils = this.$utils();
    var str = formatStr || FORMAT_DEFAULT;
    var result = str.replace(/\[([^\]]+)]|Q|wo|gggg|Do|X|x|k{1,2}|S/g, function (match) {
      switch (match) {
        case 'Q':
          return Math.ceil((_this.$M + 1) / 3);

        case 'Do':
          return locale.ordinal(_this.$D);

        case 'gggg':
          return _this.weekYear();

        case 'wo':
          return locale.ordinal(_this.week(), 'W');
        // W for week

        case 'k':
        case 'kk':
          return utils.s(String(_this.$H === 0 ? 24 : _this.$H), match === 'k' ? 1 : 2, '0');

        case 'X':
          return Math.floor(_this.$d.getTime() / 1000);

        case 'x':
          return _this.$d.getTime();

        default:
          return match;
      }
    });
    return oldFormat.bind(this)(result);
  };
});

var formattingTokens = /(\[[^[]*\])|([-:/.()\s]+)|(A|a|YYYY|YY?|MM?M?M?|Do|DD?|hh?|HH?|mm?|ss?|S{1,3}|z|ZZ?)/g;
var match1 = /\d/; // 0 - 9

var match2 = /\d\d/; // 00 - 99

var match3 = /\d{3}/; // 000 - 999

var match4 = /\d{4}/; // 0000 - 9999

var match1to2 = /\d\d?/; // 0 - 99

var matchUpperCaseAMPM = /[AP]M/;
var matchLowerCaseAMPM = /[ap]m/;
var matchSigned = /[+-]?\d+/; // -inf - inf

var matchOffset = /[+-]\d\d:?\d\d/; // +00:00 -00:00 +0000 or -0000

var matchWord = /\d*[^\s\d-:/.()]+/; // Word

var locale;

function offsetFromString(string) {
  var parts = string.match(/([+-]|\d\d)/g);
  var minutes = +(parts[1] * 60) + +parts[2];
  return minutes === 0 ? 0 : parts[0] === '+' ? -minutes : minutes; // eslint-disable-line no-nested-ternary
}

var addInput = function addInput(property) {
  return function (input) {
    this[property] = +input;
  };
};

var zoneExpressions = [matchOffset, function (input) {
  var zone = this.zone || (this.zone = {});
  zone.offset = offsetFromString(input);
}];
var expressions = {
  A: [matchUpperCaseAMPM, function (input) {
    this.afternoon = input === 'PM';
  }],
  a: [matchLowerCaseAMPM, function (input) {
    this.afternoon = input === 'pm';
  }],
  S: [match1, function (input) {
    this.milliseconds = +input * 100;
  }],
  SS: [match2, function (input) {
    this.milliseconds = +input * 10;
  }],
  SSS: [match3, function (input) {
    this.milliseconds = +input;
  }],
  s: [match1to2, addInput('seconds')],
  ss: [match1to2, addInput('seconds')],
  m: [match1to2, addInput('minutes')],
  mm: [match1to2, addInput('minutes')],
  H: [match1to2, addInput('hours')],
  h: [match1to2, addInput('hours')],
  HH: [match1to2, addInput('hours')],
  hh: [match1to2, addInput('hours')],
  D: [match1to2, addInput('day')],
  DD: [match2, addInput('day')],
  Do: [matchWord, function (input) {
    var _locale = locale,
        ordinal = _locale.ordinal;

    var _input$match = input.match(/\d+/);

    this.day = _input$match[0];
    if (!ordinal) return;

    for (var i = 1; i <= 31; i += 1) {
      if (ordinal(i).replace(/\[|\]/g, '') === input) {
        this.day = i;
      }
    }
  }],
  M: [match1to2, addInput('month')],
  MM: [match2, addInput('month')],
  MMM: [matchWord, function (input) {
    var _locale2 = locale,
        months = _locale2.months,
        monthsShort = _locale2.monthsShort;
    var matchIndex = monthsShort ? monthsShort.findIndex(function (month) {
      return month === input;
    }) : months.findIndex(function (month) {
      return month.substr(0, 3) === input;
    });

    if (matchIndex < 0) {
      throw new Error();
    }

    this.month = matchIndex + 1;
  }],
  MMMM: [matchWord, function (input) {
    var _locale3 = locale,
        months = _locale3.months;
    var matchIndex = months.indexOf(input);

    if (matchIndex < 0) {
      throw new Error();
    }

    this.month = matchIndex + 1;
  }],
  Y: [matchSigned, addInput('year')],
  YY: [match2, function (input) {
    input = +input;
    this.year = input + (input > 68 ? 1900 : 2000);
  }],
  YYYY: [match4, addInput('year')],
  Z: zoneExpressions,
  ZZ: zoneExpressions
};

function correctHours(time) {
  var afternoon = time.afternoon;

  if (afternoon !== undefined) {
    var hours = time.hours;

    if (afternoon) {
      if (hours < 12) {
        time.hours += 12;
      }
    } else if (hours === 12) {
      time.hours = 0;
    }

    delete time.afternoon;
  }
}

function makeParser(format) {
  var array = format.match(formattingTokens);
  var length = array.length;

  for (var i = 0; i < length; i += 1) {
    var token = array[i];
    var parseTo = expressions[token];
    var regex = parseTo && parseTo[0];
    var parser = parseTo && parseTo[1];

    if (parser) {
      array[i] = {
        regex: regex,
        parser: parser
      };
    } else {
      array[i] = token.replace(/^\[|\]$/g, '');
    }
  }

  return function (input) {
    var time = {};

    for (var _i = 0, start = 0; _i < length; _i += 1) {
      var _token = array[_i];

      if (typeof _token === 'string') {
        start += _token.length;
      } else {
        var _regex = _token.regex,
            _parser = _token.parser;
        var part = input.substr(start);

        var match = _regex.exec(part);

        var value = match[0];

        _parser.call(time, value);

        input = input.replace(value, '');
      }
    }

    correctHours(time);
    return time;
  };
}

var parseFormattedInput = function parseFormattedInput(input, format, utc) {
  try {
    var parser = makeParser(format);

    var _parser2 = parser(input),
        year = _parser2.year,
        month = _parser2.month,
        day = _parser2.day,
        hours = _parser2.hours,
        minutes = _parser2.minutes,
        seconds = _parser2.seconds,
        milliseconds = _parser2.milliseconds,
        zone = _parser2.zone;

    if (zone) {
      return new Date(Date.UTC(year, month - 1, day, hours || 0, minutes || 0, seconds || 0, milliseconds || 0) + zone.offset * 60 * 1000);
    }

    var now = new Date();
    var y = year || now.getFullYear();
    var M = month > 0 ? month - 1 : now.getMonth();
    var d = day || now.getDate();
    var h = hours || 0;
    var m = minutes || 0;
    var s = seconds || 0;
    var ms = milliseconds || 0;

    if (utc) {
      return new Date(Date.UTC(y, M, d, h, m, s, ms));
    }

    return new Date(y, M, d, h, m, s, ms);
  } catch (e) {
    return new Date(''); // Invalid Date
  }
};

var customParseFormat = (function (o, C, d) {
  var proto = C.prototype;
  var oldParse = proto.parse;

  proto.parse = function (cfg) {
    var date = cfg.date,
        format = cfg.format,
        pl = cfg.pl,
        utc = cfg.utc;
    this.$u = utc;

    if (format) {
      locale = pl ? d.Ls[pl] : this.$locale();
      this.$d = parseFormattedInput(date, format, utc);
      this.init(cfg);
    } else {
      oldParse.call(this, cfg);
    }
  };
});

var dayOfYear = (function (o, c) {
  var proto = c.prototype;

  proto.dayOfYear = function (input) {
    var dayOfYear = Math.round((this.startOf('day') - this.startOf('year')) / 864e5) + 1;
    return input == null ? dayOfYear : this.add(input - dayOfYear, 'day');
  };
});

var isBetween = (function (o, c, d) {
  c.prototype.isBetween = function (a, b, u, i) {
    var dA = d(a);
    var dB = d(b);
    i = i || '()';
    var dAi = i[0] === '(';
    var dBi = i[1] === ')';
    return (dAi ? this.isAfter(dA, u) : !this.isBefore(dA, u)) && (dBi ? this.isBefore(dB, u) : !this.isAfter(dB, u)) || (dAi ? this.isBefore(dA, u) : !this.isAfter(dA, u)) && (dBi ? this.isAfter(dB, u) : !this.isBefore(dB, u));
  };
});

var isLeapYear = (function (o, c) {
  var proto = c.prototype;

  proto.isLeapYear = function () {
    return this.$y % 4 === 0 && this.$y % 100 !== 0 || this.$y % 400 === 0;
  };
});

var isMoment = (function (o, c, f) {
  f.isMoment = function (input) {
    return f.isDayjs(input);
  };
});

var isSameOrAfter = (function (o, c) {
  c.prototype.isSameOrAfter = function (that, units) {
    return this.isSame(that, units) || this.isAfter(that, units);
  };
});

var isSameOrBefore = (function (o, c) {
  c.prototype.isSameOrBefore = function (that, units) {
    return this.isSame(that, units) || this.isBefore(that, units);
  };
});

var localeData = (function (o, c, dayjs) {
  // locale needed later
  var proto = c.prototype;

  var getShort = function getShort(ins, target, full, num) {
    var locale = ins.$locale();

    if (!locale[target]) {
      return locale[full].map(function (f) {
        return f.substr(0, num);
      });
    }

    return locale[target];
  };

  var localeData = function localeData() {
    var _this = this;

    return {
      months: function months(instance) {
        return instance ? instance.format('MMMM') : getShort(_this, 'months');
      },
      monthsShort: function monthsShort(instance) {
        return instance ? instance.format('MMM') : getShort(_this, 'monthsShort', 'months', 3);
      },
      firstDayOfWeek: function firstDayOfWeek() {
        return _this.$locale().weekStart || 0;
      },
      weekdaysMin: function weekdaysMin(instance) {
        return instance ? instance.format('dd') : getShort(_this, 'weekdaysMin', 'weekdays', 2);
      },
      weekdaysShort: function weekdaysShort(instance) {
        return instance ? instance.format('ddd') : getShort(_this, 'weekdaysShort', 'weekdays', 3);
      }
    };
  };

  proto.localeData = function () {
    return localeData.bind(this)();
  };

  dayjs.localeData = function () {
    var localeObject = dayjs.Ls[dayjs.locale()];
    return {
      firstDayOfWeek: function firstDayOfWeek() {
        return localeObject.weekStart || 0;
      }
    };
  };
});

var localizedFormat = (function (o, c, d) {
  var proto = c.prototype;
  var oldFormat = proto.format;
  var englishFormats = {
    LTS: 'h:mm:ss A',
    LT: 'h:mm A',
    L: 'MM/DD/YYYY',
    LL: 'MMMM D, YYYY',
    LLL: 'MMMM D, YYYY h:mm A',
    LLLL: 'dddd, MMMM D, YYYY h:mm A'
  };
  d.en.formats = englishFormats;

  var t = function t(format) {
    return format.replace(/(\[[^\]]+])|(MMMM|MM|DD|dddd)/g, function (_, a, b) {
      return a || b.slice(1);
    });
  };

  proto.format = function (formatStr) {
    if (formatStr === void 0) {
      formatStr = FORMAT_DEFAULT;
    }

    var _this$$locale = this.$locale(),
        _this$$locale$formats = _this$$locale.formats,
        formats = _this$$locale$formats === void 0 ? {} : _this$$locale$formats;

    var result = formatStr.replace(/(\[[^\]]+])|(LTS?|l{1,4}|L{1,4})/g, function (_, a, b) {
      var B = b && b.toUpperCase();
      return a || formats[b] || englishFormats[b] || t(formats[B]);
    });
    return oldFormat.call(this, result);
  };
});

var minMax = (function (o, c, d) {
  var sortBy = function sortBy(method, dates) {
    if (!dates.length) {
      return d();
    }

    if (dates.length === 1 && dates[0].length > 0) {
      var _dates = dates;
      dates = _dates[0];
    }

    var result;
    var _dates2 = dates;
    result = _dates2[0];

    for (var i = 1; i < dates.length; i += 1) {
      if (!dates[i].isValid() || dates[i][method](result)) {
        result = dates[i];
      }
    }

    return result;
  };

  d.max = function () {
    var args = [].slice.call(arguments, 0); // eslint-disable-line prefer-rest-params

    return sortBy('isAfter', args);
  };

  d.min = function () {
    var args = [].slice.call(arguments, 0); // eslint-disable-line prefer-rest-params

    return sortBy('isBefore', args);
  };
});

var weekYear = (function (option, Dayjs, dayjs) {
  var proto = Dayjs.prototype;

  dayjs.utc = function (date, format) {
    var cfg = {
      date: date,
      utc: true,
      format: format
    };
    return new Dayjs(cfg); // eslint-disable-line no-use-before-define
  };

  proto.utc = function () {
    return dayjs(this.toDate(), {
      locale: this.$L,
      utc: true
    });
  };

  proto.local = function () {
    return dayjs(this.toDate(), {
      locale: this.$L,
      utc: false
    });
  };

  var oldParse = proto.parse;

  proto.parse = function (cfg) {
    if (cfg.utc) {
      this.$u = true;
    }

    oldParse.call(this, cfg);
  };

  var oldInit = proto.init;

  proto.init = function () {
    if (this.$u) {
      var $d = this.$d;
      this.$y = $d.getUTCFullYear();
      this.$M = $d.getUTCMonth();
      this.$D = $d.getUTCDate();
      this.$W = $d.getUTCDay();
      this.$H = $d.getUTCHours();
      this.$m = $d.getUTCMinutes();
      this.$s = $d.getUTCSeconds();
      this.$ms = $d.getUTCMilliseconds();
    } else {
      oldInit.call(this);
    }
  };

  var oldUtcOffset = proto.utcOffset;

  proto.utcOffset = function () {
    if (this.$u) {
      return 0;
    }

    return oldUtcOffset.call(this);
  };

  var oldFormat = proto.format;
  var UTC_FORMAT_DEFAULT = 'YYYY-MM-DDTHH:mm:ss[Z]';

  proto.format = function (formatStr) {
    var str = formatStr || (this.$u ? UTC_FORMAT_DEFAULT : '');
    return oldFormat.call(this, str);
  };

  proto.isUTC = function () {
    return !!this.$u;
  };
});

var weekday = (function (o, c) {
  var proto = c.prototype;

  proto.weekday = function (input) {
    var weekStart = this.$locale().weekStart || 0;
    var $W = this.$W;
    var weekday = ($W < weekStart ? $W + 7 : $W) - weekStart;

    if (this.$utils().u(input)) {
      return weekday;
    }

    return this.subtract(weekday, 'day').add(input, 'day');
  };
});

var weekOfYear = (function (o, c, d) {
  var proto = c.prototype;

  proto.week = function (week) {
    if (week === void 0) {
      week = null;
    }

    if (week !== null) {
      return this.add((week - this.week()) * 7, 'day');
    }

    var weekStart = this.$locale().weekStart || 0; // d(this) clone is for badMutable plugin

    var endOfYear = d(this).endOf(Y);

    if (weekStart === 0 && endOfYear.day() !== 6 && this.month() === 11 && 31 - this.date() <= endOfYear.day()) {
      return 1;
    }

    var startOfYear = d(this).startOf(Y);
    var compareDay = startOfYear.subtract(startOfYear.day() - weekStart, D).subtract(1, MS);
    var diffInWeek = this.diff(compareDay, W, true);
    return Math.ceil(diffInWeek);
  };

  proto.weeks = function (week) {
    if (week === void 0) {
      week = null;
    }

    return this.week(week);
  };
});

/**
 * This file is used to build a custom version of Day.JS that is compiled and optimized with
 * all the necessary functions we need in the Expression Engine to reproducd Moment.js
 * features.
 */

dayjs.extend(advancedFormat);
dayjs.extend(customParseFormat);
dayjs.extend(dayOfYear);
dayjs.extend(isBetween);
dayjs.extend(isLeapYear);
dayjs.extend(isMoment);
dayjs.extend(isSameOrAfter);
dayjs.extend(isSameOrBefore);
dayjs.extend(localeData);
dayjs.extend(localizedFormat);
dayjs.extend(minMax);
dayjs.extend(weekYear);
dayjs.extend(weekday);
dayjs.extend(weekOfYear);
dayjs.extend(weekYear);


/************************** Expressions *********************/
const EXPRESSIONS = {
    NULL: null,

    EQUALS: function(left, right) {
        if (Object.prototype.toString.call(left) === '[object Date]' && Object.prototype.toString.call(right) === '[object Date]') {
            return EXPRESSIONS.EQUALS(left.getTime(), right.getTime());
        }
        return left == right;
    },
    NOTEQUALS: function(left, right) {
        return !EXPRESSIONS.EQUALS(left, right);
    },
    COMPARE: function(operator, left, right) {
        /* jshint eqnull:true */
        if ((left == null && right) || (right == null && left)) {
            return false;
        }
        switch (operator) {
            case '<':
                return left < right;
            case '<=':
                return left <= right;
            case '>':
                return left > right;
            case '>=':
                return left >= right;
            default:
                return false;
        }
    },

    AND: function() {
        if (arguments.length == 0) {
            return false;
        }
        for (var i = 0; i < arguments.length; i++) {
            if (!arguments[i]) {
                return false;
            }
        }
        return true;
    },

    OR: function() {
        if (arguments.length == 0) {
            return false;
        }
        for (var i = 0; i < arguments.length; i++) {
            if (arguments[i]) {
                return true;
            }
        }
        return false;
    },

    /* TEXT FUNCTIONS */
    STRING: function(value) {
        /* jshint eqnull:true */
        if (value == null) {
            return '';
        }
        return '' + value;
    },
    NUMBER: function(value, handleDate) {
        /* jshint eqnull:true */
        if (isNaN(value) || value == null) {
            return 0;
        }
        if (Object.prototype.toString.call(value) === '[object Date]') {
            return handleDate ? value : null;
        }
        return parseFloat(value);
    },
    INTEGER: function(value) {
        /* jshint eqnull:true */
        if (isNaN(value) || value == null) {
            return 0;
        }
        return parseInt(value);
    },
    CURRENCY: function(value) {
        return EXPRESSIONS.NUMBER(value);
    },
    BOOLEAN: function(value) {
        return value ? true : false;
    },
    RANDOM: function() {
        return EXPRESSIONS.NUMBER(Math.random());
    },
    DATE: function(value) {
        if (Object.prototype.toString.call(value) === '[object Date]') {
            return value;
        } else if (dayjs.isMoment(value)) {
            return value.toDate();
        } else if (arguments.length > 1) {
            var array = EXPRESSIONS.ARRAY(arguments);
            if (array[0] == null || array[1] == null || array[2] == null) {
                return null;
            }
            array[1] = array[1] - 1; // increase month by 1
            return new (Function.prototype.bind.apply(Date, [null].concat(array)))();
        } else if (typeof value === 'string' || value instanceof String) {
            return new Date(value);
        }
        return NaN;
    },
    ARRAY: function(values) {
        if (values === undefined) {
            return [];
        } else if (values === null) {
            return [null];
        }
        // if args we need to see how many items - if one assume pass in
        if (Object.prototype.toString.call(values) === '[object Arguments]') {
            if (values.length === 1) {
                return EXPRESSIONS.ARRAY(values[0]);
            } else {
                return [].slice.call(values, 0);
            }
        }
        if (Object.prototype.toString.call(values) !== '[object Array]') {
            return [values];
        }

        return values;
    },
    MOMENT: dayjs,
    CONCATENATE: function(values) {
        var array = arguments;
        if (arguments.length === 1) {
            array = EXPRESSIONS.ARRAY(arguments[0]);
        }
        return [].reduce.call(
            array,
            function(currentValue, next) {
                return currentValue + EXPRESSIONS.STRING(next);
            },
            '',
        );
    },
    CASE: function(text, case_type) {
        text = EXPRESSIONS.STRING(text);
        switch (case_type) {
            case EXPRESSIONS.UPPER:
                return text.toUpperCase();
            case EXPRESSIONS.LOWER:
                return text.toLowerCase();
            case EXPRESSIONS.SENTENCE:
                return text.charAt(0).toUpperCase() + text.toLowerCase().slice(1);
            case EXPRESSIONS.TITLE:
                return text.replace(/\w\S*/g, function(txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                });
            default:
                throw new Error('Invalid argument to CASE: ' + case_type);
        }
    },
    SUBSTRING: function(text, startIndex, endIndex) {
        text = EXPRESSIONS.STRING(text);
        return text.substring(startIndex, endIndex);
    },
    SPLIT: function(text, splitToken, limit) {
        text = EXPRESSIONS.STRING(text);
        if (limit != null) {
            return text.split(splitToken, limit);
        }
        return text.split(splitToken);
    },
    UPPER: 'UPPER',
    LOWER: 'LOWER',
    SENTENCE: 'SENTENCE',
    TITLE: 'TITLE',
    upper: 'UPPER',
    lower: 'LOWER',
    sentence: 'SENTENCE',
    title: 'TITLE',

    /* AGGREGATE ARRAY FUNCTIONS */
    SUM: function(values) {
        return EXPRESSIONS.ARRAY(arguments).reduce(function(currentVal, next) {
            /* jshint eqnull:true */
            if (next == null) {
                next = 0;
            }
            return EXPRESSIONS.NUMBER(currentVal, true) + EXPRESSIONS.NUMBER(next, true);
        }, 0);
    },
    SUMIF: function(values, expression_or_value) {
        if (typeof expression_or_value !== 'function') {
            var isEqualTo = expression_or_value;
            expr = function(element) {
                return element == isEqualTo;
            };
        }
        return EXPRESSIONS.SUM(EXPRESSIONS.ARRAY(values).filter(expression_or_value));
    },
    COUNT: function(values) {
        if (values == null) {
            return 0;
        }
        return EXPRESSIONS.ARRAY(arguments).length;
    },
    COUNTIF: function(values, expression_or_value) {
        if (typeof expression_or_value !== 'function') {
            var isEqualTo = expression_or_value;
            expression_or_value = function(element) {
                return element == isEqualTo;
            };
        }
        return EXPRESSIONS.COUNT(EXPRESSIONS.ARRAY(values).filter(expression_or_value));
    },
    AVERAGE: function(values) {
        if (EXPRESSIONS.COUNT(arguments) === 0 || (arguments.length === 1 && arguments[0] === null)) {
            return 0;
        }
        return EXPRESSIONS.SUM(arguments) / EXPRESSIONS.COUNT(arguments);
    },
    MAX: function(values) {
        var array = EXPRESSIONS.ARRAY(arguments);
        if (array.length === 0 || (arguments.length === 1 && arguments[0] === null)) return 0;
        var currentMax = EXPRESSIONS.NUMBER(array[0], true);
        array.forEach(function(val) {
            /* jshint eqnull:true */
            if (val == null) return;
            currentMax = currentMax < EXPRESSIONS.NUMBER(val, true) ? EXPRESSIONS.NUMBER(val, true) : currentMax;
        });
        return currentMax;
    },
    MIN: function(values) {
        var array = EXPRESSIONS.ARRAY(arguments);
        if (array.length === 0 || (arguments.length === 1 && arguments[0] === null)) return 0;
        var currentMin = EXPRESSIONS.NUMBER(array[0], true);
        array.forEach(function(val) {
            /* jshint eqnull:true */
            if (val == null) return;
            currentMin = currentMin > EXPRESSIONS.NUMBER(val, true) ? EXPRESSIONS.NUMBER(val, true) : currentMin;
        });
        return currentMin;
    },
    EXISTS: function(values, expression_or_value) {
        if (typeof expression_or_value === 'function') {
            return EXPRESSIONS.ARRAY(values).some(expression_or_value);
        }
        return EXPRESSIONS.ARRAY(values).includes(expression_or_value);
    },
    CONTAINS: function(input_string, value) {
        return EXPRESSIONS.STRING(input_string).indexOf(value) > -1;
    },

    /* MATH FUNCTIONS */
    ROUND: function(number, num_digits) {
        if (typeof num_digits === 'undefined') return EXPRESSIONS.ROUND(number, 0);

        number = EXPRESSIONS.NUMBER(number);
        num_digits = EXPRESSIONS.NUMBER(num_digits);

        if (isNaN(number) || !(typeof num_digits === 'number' && num_digits % 1 === 0)) return NaN;

        // Shift
        number = number.toString().split('e');
        number = Math.round(+(number[0] + 'e' + (number[1] ? +number[1] + num_digits : num_digits)));

        // Shift back
        number = number.toString().split('e');
        return +(number[0] + 'e' + (number[1] ? +number[1] - num_digits : -num_digits));
    },
    ABS: function(number) {
        /* jshint eqnull:true */
        if (number == null) {
            number = 0;
        }
        return Math.abs(EXPRESSIONS.NUMBER(number));
    },
    POW: function(base, exponent) {
        /* jshint eqnull:true */
        if (base == null) {
            base = 0;
        }
        /* jshint eqnull:true */
        if (exponent == null) {
            exponent = 0;
        }
        return Math.pow(EXPRESSIONS.NUMBER(base), EXPRESSIONS.NUMBER(exponent));
    },

    /* DATE FUNCTIONS */
    NOW: function() {
        // to not cause infinite digest in OmniScript we strip the milliseconds
        var now = new Date();
        now.setMilliseconds(0);
        return now;
    },
    TODAY: function() {
        var now = EXPRESSIONS.NOW();
        return new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
    },
    AGE: function(birth_date) {
        return EXPRESSIONS.AGEON(birth_date, EXPRESSIONS.NOW());
    },
    AGEON: function(birth_date, on_date) {
        birth_date = EXPRESSIONS.DATE(birth_date);
        on_date = EXPRESSIONS.DATE(on_date);
        if (isNaN(birth_date) || isNaN(on_date)) {
            return EXPRESSIONS.NULL;
        }
        if (birth_date >= on_date) {
            return 0;
        }
        var age = on_date.getFullYear() - birth_date.getFullYear();
        var m = on_date.getMonth() - birth_date.getMonth();
        if (m < 0 || (m === 0 && on_date.getDate() < birth_date.getDate())) {
            age--;
        }
        return age;
    },
    DATEDIFF: function(date1, date2) {
        /* jshint eqnull:true */
        if (date1 == null || date2 == null) {
            return null;
        }
        return EXPRESSIONS.ROUND((EXPRESSIONS.DATE(date2) - EXPRESSIONS.DATE(date1)) / (1000 * 60 * 60 * 24));
    },
    DAYOFMONTH: function(date) {
        /* jshint eqnull:true */
        if (date == null) {
            return null;
        }
        return EXPRESSIONS.DATE(date).getDate();
    },
    DAYOFWEEK: function(date) {
        /* jshint eqnull:true */
        if (date == null) {
            return null;
        }
        var dayOfWeek = EXPRESSIONS.DATE(date).getDay();
        if (dayOfWeek === 0) dayOfWeek = 7;
        return dayOfWeek;
    },
    MONTH: function(date) {
        /* jshint eqnull:true */
        if (date == null) {
            return null;
        }
        return EXPRESSIONS.DATE(date).getMonth() + 1;
    },
    YEAR: function(date) {
        /* jshint eqnull:true */
        if (date == null) {
            return null;
        }
        return EXPRESSIONS.DATE(date).getFullYear();
    },
    HOUR: function(date) {
        /* jshint eqnull:true */
        if (date == null) {
            return null;
        }
        return EXPRESSIONS.DATE(date).getHours();
    },
    MINUTE: function(date) {
        /* jshint eqnull:true */
        if (date == null) {
            return null;
        }
        return EXPRESSIONS.DATE(date).getMinutes();
    },

    /* IF/THEN/ELSE */
    IF: function(expression, thenValue, elseValue) {
        if (expression) {
            return thenValue;
        }
        return elseValue;
    },
};

erformsFrmlEngine.Parser = Parser;
erformsFrmlEngine.ParserRules = ParserRules
erformsFrmlEngine.setRandomGenerator = setRandomGenerator
erformsFrmlEngine.ParserStart = ParserStart;
erformsFrmlEngine.setTemplateResolverFn = setTemplateResolverFn;

})(erformsFrmlEngine);