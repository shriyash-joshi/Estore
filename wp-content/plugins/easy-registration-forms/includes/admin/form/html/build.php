<?php
// Enqueue scripts  
wp_enqueue_script('erf-font-awesome-icon-picker');
wp_enqueue_script('erf-masked-input');
wp_enqueue_script('vuejs_sortable');
wp_enqueue_script('vuejs_draggable');
wp_enqueue_script('erf-builder');

// Enqueue Styles
wp_enqueue_style('erf-font-awesome-css');
wp_enqueue_style('erf-font-awesome-icon-picker');
wp_enqueue_style('erf-builder-style');

require('fields/field_option_tags.html');
require('fields/field_tag.html');
?>
<div class="erf-form-builder-wrapper" id="erf_fb_app">
    <input type="text" class="erf-input-field erf-input-field-title" v-model="title" id="erforms-form-title" placeholder="<?php echo __('Form Name', 'erforms'); ?>" />
    <div class="erf-feature-request">
        <p class="description erf-form-code"><?php _e('Paste the following shortcode inside a post or page.', 'erforms'); ?> <input id="form-code" title="Click to copy Shortcode" type="text" readonly value='[erforms id="<?php echo $form_id; ?>"]'> <span style="display:none" id="copy-message"><?php _e('Copied to Clipboard', 'erforms'); ?></span></p>
    </div>
    
    <div id="erf_progress" style="display:none"><div class="loader">
            <img src="<?php echo ERFORMS_PLUGIN_URL.'/assets/admin/images/loader.svg' ?>">
        </div></div>
    <div class="erf-form-builder">
         <div class="erf-field-type-container">
             <h4 class="erf-field-heading"><?php _e('Available Fields', 'erforms'); ?></h4>
        <div class="erf-field-types">
            <div class="erf-field-group" @click="showInputFields= !showInputFields" v-bind:class="{ 'erf-right-arrow': !showInputFields,'erf-down-arrow':showInputFields }">
                <?php _e('Input Fields','erforms'); ?>
            </div>
            <vuedraggable  v-if="showInputFields" class="dragArea list-group"
                          :sort="false"
                          :list="inputFields" 
                          :group="{name:'fields',pull:'clone',put:false }"
                          :clone="cloneField">
                <div class="list-group-item erf-field-type" v-for="field in inputFields" :key="field.key">
                  <a href="javascript:void(0)"><span class="erf-drag-handle"></span>{{field.label}}</a>
                </div>
            </vuedraggable>
        </div>
         
        <div class="erf-field-types">
            <div class="erf-field-group" @click="showDisplayFields= !showDisplayFields" v-bind:class="{ 'erf-right-arrow': !showDisplayFields,'erf-down-arrow':showDisplayFields }">
                <?php _e('Display Fields','erforms'); ?>
                <span></span>
            </div>
            <vuedraggable v-if="showDisplayFields" class="dragArea list-group"
                          :sort="false"
                          :list="displayFields" 
                          :group="{name:'fields',pull:'clone',put:false }"
                          :clone="cloneField">
                <div class="list-group-item erf-field-type" v-for="field in displayFields" :key="field.key">
                  <a href="javascript:void(0)"><span class="erf-drag-handle"></span>{{field.label}}</a>
                </div>
            </vuedraggable>
        </div>
             
        <div class="erf-field-types">
            <div class="erf-field-group" @click="showFunctionFields= !showFunctionFields" v-bind:class="{ 'erf-right-arrow': !showFunctionFields,'erf-down-arrow':showFunctionFields }">
                <?php _e('Function Fields','erforms'); ?>
                <span></span>
            </div>
            <vuedraggable v-if="showFunctionFields" class="dragArea list-group"
                          :sort="false"
                          :list="functionFields" 
                          :group="{name:'fields',pull:'clone',put:false }"
                          :clone="cloneField">
                <div class="list-group-item erf-field-type" v-for="field in functionFields" :key="field.key">
                  <a href="javascript:void(0)"><span class="erf-drag-handle"></span>{{field.label}}</a>
                </div>
            </vuedraggable>
        </div>     
         
         </div>
         
        <div class="erf-form-fields">
            <vuedraggable 
                draggable=".erf-field-row" 
                @change="onDragDrop($event)" 
                :disabled="dragDisabled"
                class="dragArea list-group erf-fields-container"
                :list="fields"
                group="fields">
                <div v-for="field, index in fields" class="erf-field-row" v-bind:key="field.uniqueID" v-bind:class="{ 'invalid-field': hasErrors(field) }">
                    <erf_select_field
                        v-if="field.type=='select' || field.type=='radio-group' || field.type=='checkbox-group' || field.type=='state' || field.type=='country'"
                        v-bind:prop-attrs="field" 
                        v-bind:field-index="index" 
                        v-on:label-change="onLabelChange"
                        v-on:update="onUpdate"
                        v-on:option-update="onOptionUpdate"
                        v-on:option-add="onOptionAdd"
                        v-on:edit="onEditField"
                        v-on:duplicate="onDuplicateField"
                        v-on:delete="onDeleteField"
                        v-on:hide-edit="onHideEdit"
                        v-on:option-remove="onOptionRemove">
                    </erf_select_field>

                    <erf_field  
                        v-else
                        v-bind:prop-attrs="field" 
                        v-bind:field-index="index"
                        v-on:update="onUpdate"
                        v-on:label-change="onLabelChange"
                        v-on:option-add="onOptionAdd"
                        v-on:edit="onEditField"
                        v-on:duplicate="onDuplicateField"
                        v-on:delete="onDeleteField"
                        v-on:hide-edit="onHideEdit"
                        v-on:option-remove="onOptionRemove">
                    </erf_field>
                </div>
            </vuedraggable>   
        </div>
     </div>   
    <p class="form_error" v-if="invalidForm">There are errors on the form. Please fix them before continuing.</p>
    <p class="submit">
        <input type="button" @click="saveForm()" value="<?php _e('Save', 'erforms'); ?>" class="button button-primary" id="erforms-form-save-btn" /> 
        <input type="button" @click="saveForm(true)" value="<?php _e('Save & Close', 'erforms'); ?>" class="button button-primary" id="erforms-form-save-close-btn" />
    </p>
</div>
