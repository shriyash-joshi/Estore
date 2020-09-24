<?php
    Yii::app()->clientScript->registerScriptFile('//univariety.sgp1.digitaloceanspaces.com/js/angular/angular.v1.7.8.min.js');
    Yii::app()->clientScript->registerScriptFile('//univariety.sgp1.digitaloceanspaces.com/js/angular/angular.sanitize.v1.7.8.js');
    Yii::app()->clientScript->registerScriptFile('//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=AIzaSyDDUvauOVbQv5OdDEFR8GqPzspt_1KL15o');
    
?>
<style type="text/css">
    div.question-template input, div.question-template textarea {background: #ffffff;}
    input[type=checkbox], input[type=radio] {margin: 6px 0 0;}
</style>

<div ng-controller="CurriculumCourse" style="min-height: 450px;">
    <div class="container pt-4 pb-4">
        <form id="question-form">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <div ng-if="question.id;">

                        <div ng-if="question.type !== 'Questions'">
                            <question></question>
                        </div>

                        <div ng-if="question.type == 'Questions'">
                            <h4>{{question.title}}</h4>
                            <question ng-repeat="question in question.Questions"></question>
                        </div>

                        <div class="form-group" ng-if="question.id;">
                            <button 
                                ng-if="index > 0"
                                ng-click="prev()"
                                class="btn btn-success white-text" 
                                style="padding-right: 30px; padding-left: 30px;">
                                Back
                            </button>

                            <button 
                                ng-click="next()"
                                ng-if="index < total"
                                class="btn btn-success white-text pull-right btn-next" 
                                style="padding-right: 30px; padding-left: 30px;">
                                {{(index+1 == total) ? "Finish" : "Next"}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<script type="text/javascript">
    
    var uniApp = angular.module('uniApp', ['ngSanitize']);
    
    uniApp.directive('inputText', function(){
        return {
            restrict: 'E',
            template: '<input ng-focus="question.error = false" placeholder="{{question.placeholder}}" '
                    + 'type="text" ng-model="question.value" class="form-control {{question.css_class}}" />'
        };
    });
    
    uniApp.directive('question', function(){
        return {
            restrict: 'E',
            templateUrl: '<?php echo $this->createUrl('questionTemplate', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []); ?>'
        };
    });
    
    uniApp.directive('inputDate', function(){
        return {
            restrict: 'E',
            template: '<div class="input-group">'
                    + '<input ng-focus="question.error = false" placeholder="{{question.placeholder}}" '
                    + 'type="text" readonly="readonly" ng-model="question.value" class="form-control date-picker" />'
                    + '<span class="input-group-addon" onclick="$(\'.date-picker\').trigger(\'click\')">'
                    + '<i class="fa fa-calendar fa-2x"></i>'
                    + '</span></div>'
        };
    });
    
    uniApp.directive('inputRadio', function(){
        return {
            restrict: 'E',
            template: '<label>'
                    + '<input type="radio" name="{{question.id}}" '
                    + 'ng-click="onRadioClick(question.options, option); question.error = false" '
                    + 'ng-checked="option.selected" />'
                    + '{{option.label}}'
                    + '</label>'
        };
    });
    
    
    uniApp.directive('inputCheckbox', function(){
        return {
            restrict: 'E',
            template: '<label>'
                    + '<input type="checkbox" ng-model="option.selected" ng-click="question.error = false">'
                    + '{{option.label}}'
                    + '</label>'
        };
    });
    
    
    uniApp.controller('CurriculumCourse', ['$scope', '$http', '$sce', function($scope, $http, $sce){

        $scope.index = 0;
        $scope.question = null;
        
        $scope.total = <?php echo $CurriculumCourse->getQuestionsTotal(); ?>;
        
        $scope.prev = function(){
            getQuestion($scope.index-1, true);
        };
        
        $scope.next = function(){
            getQuestion($scope.index+1, false);
        };
        
        function getQuestion(index, previous){
            if(index > $scope.total) reutrn;
            var url = '<?php echo $this->createUrl('nextQuestion', filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>';
            $http.post(url, {i:index, q:$scope.question, p:previous}).then(function(response){
                if(response.data.end){
                    $scope.question = null;
                    
                    if(response.data.generate){
                        window.location = '<?php echo $this->createUrl('generateReport', 
                                filter_input_array(INPUT_GET) ? filter_input_array(INPUT_GET) : []) ?>';
                    }else{
                        window.location.reload();
                    }
                }
                $scope.question = response.data.q;
                setTimeout(function(){
                    if($("div.has-error:visible").length > 0){
                        $("html, body").animate({ scrollTop: $("div.has-error:visible").first().offset().top - 150 }, 1000);
                    }else{
                        $("html, body").animate({ scrollTop: 0}, 600);
                    }
                    //if(response.data.i+1 < $scope.total) $('.btn-next').trigger('click');
                }, 250);
                $scope.index = response.data.i;
            });            
        }
        
        $scope.onRadioClick = function(options, option){
            $scope.question.error = false;
            angular.forEach(options, function(o, k){
                o.selected = false;
            });
            
            option.selected = true;
            
            if($('.question-template').length > 1){
                var y = $(window).scrollTop();
                var to = 54*options.length;
                $("html, body").animate({ scrollTop: y + to}, 600);
            }
            
        };

        getQuestion(<?php echo $index; ?>, false);
    }]);
    
    $('body').on('click', '.date-picker', function(){
        $(this).datetimepicker({
            format:'Y-m-d',
            formatDate: 'Y-m-d', 
            maxDate:'<?php echo date('Y-m-d', strtotime('-1 Years')) ?>', 
            timepicker:false, 
            autoclose:true, 
            closeOnDateSelect:true
        });
        $(this).datetimepicker('show');
    });
    
    function initialize(element) {
        var autocomplete = new google.maps.places.Autocomplete(element, {types: ['geocode']});
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            setTimeout(function() {
                for (var i = 0; i < place.address_components.length; i++) {
                    var addressType = place.address_components[i].types[0];
                    var value = place.address_components[i]['long_name'];
                    
                    if(addressType == 'locality'){
                        $('.g_city_name').val(value);
                        $('.g_city_name').trigger('change');
                    }
                    
                    if(addressType == 'administrative_area_level_1'){
                        $('.g_state_name').val(value);
                        $('.g_state_name').trigger('change');
                    }
                    
                    if(addressType == 'country'){
                        $('.g_country_name').val(value);
                        $('.g_country_name').trigger('change');
                    }
                }
            }, 100);
        });
    }
    
    $('body').on('focus', '.g_autocomplete', function() {
        initialize(this);
    });
</script>