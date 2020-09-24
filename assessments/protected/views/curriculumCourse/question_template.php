
<div ng-class="{'has-error': question.error}" class="form-group has-feedback well question-template">
    <label class="control-label">{{question.title}}</label>
    
    <input-text ng-if="question.type == 'InputText' || question.type == 'InputMobile' || question.type == 'InputEmail'"></input-text>
    <input-date ng-if="question.type == 'InputDate'"></input-date>
    
    <div ng-if="question.type == 'InputRadio'">
        
        <div class="" ng-if="question.options.length <= 5 || question.inline"
             style="margin-top: 10px;"
             ng-class="question.inline ? 'radio-inline' : 'radio'"
             ng-repeat="option in question.options">
            <input-radio></input-radio>
        </div>
        
        <div class="row" ng-if="question.options.length > 5 && !question.inline">
            <div class="col-sm-6">
                <div class="" ng-class="question.inline ? 'radio-inline' : 'radio'"
                     style="margin-top: 10px;"
                     ng-repeat="option in question.options
                                 | limitTo:question.options.length / 2 + (question.options.length % 2):0">
                    <input-radio></input-radio>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="" ng-class="question.inline ? 'radio-inline' : 'radio'"
                     style="margin-top: 10px;"
                     ng-repeat="option in question.options
                                 | limitTo:question.options.length:question.options.length/2+(question.options.length%2)">
                    <input-radio></input-radio>
                </div>
            </div>
        </div>
        
    </div>
    
    <div ng-if="question.type == 'InputCheckbox'">
        
        <div class="" ng-if="question.options.length <= 5"
             style="margin-top: 10px;"
             ng-class="question.inline ? 'checkbox-inline' : 'checkbox'"
             ng-repeat="option in question.options">
            <input-checkbox></input-checkbox>
        </div>
        
        <div class="row" ng-if="question.options.length > 5">
            <div class="col-sm-6">
                <div class="" ng-class="question.inline ? 'checkbox-inline' : 'checkbox'"
                     style="margin-top: 10px;"
                     ng-repeat="option in question.options
                                 | limitTo:question.options.length / 2 + (question.options.length % 2):0">
                    <input-checkbox></input-checkbox>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="" ng-class="question.inline ? 'checkbox-inline' : 'checkbox'"
                     style="margin-top: 10px;"
                     ng-repeat="option in question.options
                                 | limitTo:question.options.length:question.options.length/2+(question.options.length%2)">
                    <input-checkbox></input-checkbox>
                </div>
            </div>
        </div>
    </div>
    
    <div ng-if="question.type == 'TextArea'">
        <textarea 
            class="form-control" 
            ng-model="question.value"
            ng-focus="question.error = false"
            placeholder="{{question.placeholder}}" rows="4"></textarea>
    </div>
    
    <p class="text-danger" ng-if="question.error">{{question.error_message}}</p>
</div>

