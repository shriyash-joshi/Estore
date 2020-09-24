<?php
if (!class_exists('WT_Sequentialordnum_Uninstall_Feedback')) :

    /**
     * Class for catch Feedback on uninstall
     */
    class WT_Sequentialordnum_Uninstall_Feedback {

        public function __construct() {
            add_action('admin_footer', array($this, 'deactivate_scripts'));
            add_action('wp_ajax_wtsequentialordnum_submit_uninstall_reason', array($this, "send_uninstall_reason"));
        }

        private function get_uninstall_reasons() {

            $reasons = array(
                array(
                    'id' => 'could-not-understand',
                    'text' => __('I couldn\'t understand how to make it work', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Would you like us to assist you?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'found-better-plugin',
                    'text' => __('I found a better plugin', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'text',
                    'placeholder' => __('Which plugin?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'not-have-that-feature',
                    'text' => __('The plugin is great, but I need specific feature that you don\'t support', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us more about that feature?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'is-not-working',
                    'text' => __('The plugin is not working', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more whats not working?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'looking-for-other',
                    'text' => __('It\'s not what I was looking for', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => 'Could you tell us a bit more?'
                ),
                array(
                    'id' => 'did-not-work-as-expected',
                    'text' => __('The plugin didn\'t work as expected', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('What did you expect?', 'wt-woocommerce-sequential-order-numbers')
                ),
                array(
                    'id' => 'other',
                    'text' => __('Other', 'wt-woocommerce-sequential-order-numbers'),
                    'type' => 'textarea',
                    'placeholder' => __('Could you tell us a bit more?', 'wt-woocommerce-sequential-order-numbers')
                ),
            );

            return $reasons;
        }

        public function deactivate_scripts() {

            global $pagenow;
            if ('plugins.php' != $pagenow) {
                return;
            }
            $reasons = $this->get_uninstall_reasons();
            ?>
            <div class="wtsequentialordnum-modal" id="wtsequentialordnum-wtsequentialordnum-modal">
                <div class="wtsequentialordnum-modal-wrap">
                    <div class="wtsequentialordnum-modal-header">
                        <h3><?php _e('If you have a moment, please let us know why you are deactivating:', 'wt-woocommerce-sequential-order-numbers'); ?></h3>
                    </div>
                    <div class="wtsequentialordnum-modal-body">
                        <ul class="reasons">
                            <?php foreach ($reasons as $reason) { ?>
                                <li data-type="<?php echo esc_attr($reason['type']); ?>" data-placeholder="<?php echo esc_attr($reason['placeholder']); ?>">
                                    <label><input type="radio" name="selected-reason" value="<?php echo $reason['id']; ?>"> <?php echo $reason['text']; ?></label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="wtsequentialordnum-modal-footer">
                        <a href="#" class="dont-bother-me"><?php _e('I rather wouldn\'t say', 'wt-woocommerce-sequential-order-numbers'); ?></a>
                        <button class="button-primary wtsequentialordnum-model-submit"><?php _e('Submit & Deactivate', 'wt-woocommerce-sequential-order-numbers'); ?></button>
                        <button class="button-secondary wtsequentialordnum-model-cancel"><?php _e('Cancel', 'wt-woocommerce-sequential-order-numbers'); ?></button>
                    </div>
                </div>
            </div>

            <style type="text/css">
                .wtsequentialordnum-modal {
                    position: fixed;
                    z-index: 99999;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    background: rgba(0,0,0,0.5);
                    display: none;
                }
                .wtsequentialordnum-modal.modal-active {display: block;}
                .wtsequentialordnum-modal-wrap {
                    width: 50%;
                    position: relative;
                    margin: 10% auto;
                    background: #fff;
                }
                .wtsequentialordnum-modal-header {
                    border-bottom: 1px solid #eee;
                    padding: 8px 20px;
                }
                .wtsequentialordnum-modal-header h3 {
                    line-height: 150%;
                    margin: 0;
                }
                .wtsequentialordnum-modal-body {padding: 5px 20px 20px 20px;}
                .wtsequentialordnum-modal-body .input-text,.wtsequentialordnum-modal-body textarea {width:75%;}
                .wtsequentialordnum-modal-body .reason-input {
                    margin-top: 5px;
                    margin-left: 20px;
                }
                .wtsequentialordnum-modal-footer {
                    border-top: 1px solid #eee;
                    padding: 12px 20px;
                    text-align: right;
                }
            </style>
            <script type="text/javascript">
                (function ($) {
                    $(function () {
                        var modal = $('#wtsequentialordnum-wtsequentialordnum-modal');
                        var deactivateLink = '';
                        $('#the-list').on('click', 'a.wtsequentialordnum-deactivate-link', function (e) {
                            e.preventDefault();
                            modal.addClass('modal-active');
                            deactivateLink = $(this).attr('href');
                            modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                        });
                        modal.on('click', 'button.wtsequentialordnum-model-cancel', function (e) {
                            e.preventDefault();
                            modal.removeClass('modal-active');
                        });
                        modal.on('click', 'input[type="radio"]', function () {
                            var parent = $(this).parents('li:first');
                            modal.find('.reason-input').remove();
                            var inputType = parent.data('type'),
                                    inputPlaceholder = parent.data('placeholder'),
                                    reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';

                            if (inputType !== '') {
                                parent.append($(reasonInputHtml));
                                parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                            }
                        });

                        modal.on('click', 'button.wtsequentialordnum-model-submit', function (e) {
                            e.preventDefault();
                            var button = $(this);
                            if (button.hasClass('disabled')) {
                                return;
                            }
                            var $radio = $('input[type="radio"]:checked', modal);
                            var $selected_reason = $radio.parents('li:first'),
                                    $input = $selected_reason.find('textarea, input[type="text"]');

                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'wtsequentialordnum_submit_uninstall_reason',
                                    reason_id: (0 === $radio.length) ? 'none' : $radio.val(),
                                    reason_info: (0 !== $input.length) ? $input.val().trim() : ''
                                },
                                beforeSend: function () {
                                    button.addClass('disabled');
                                    button.text('Processing...');
                                },
                                complete: function () {
                                    window.location.href = deactivateLink;
                                }
                            });
                        });
                    });
                }(jQuery));
            </script>
            <?php
        }

        public function send_uninstall_reason() {

            global $wpdb;

            if (!isset($_POST['reason_id'])) {
                wp_send_json_error();
            }

            $data = array(
                'reason_id' => sanitize_text_field($_POST['reason_id']),
                'plugin' => "wtseqordnum",
                'auth' => 'wtseqordnum_uninstall_1234#',
                'date' => gmdate("M d, Y h:i:s A"),
                'url' => '',
                'user_email' => '',
                'reason_info' => isset($_REQUEST['reason_info']) ? trim(stripslashes($_REQUEST['reason_info'])) : '',
                'software' => $_SERVER['SERVER_SOFTWARE'],
                'php_version' => phpversion(),
                'mysql_version' => $wpdb->db_version(),
                'wp_version' => get_bloginfo('version'),
                'wc_version' => (!defined('WC_VERSION')) ? '' : WC_VERSION,
                'locale' => get_locale(),
                'multisite' => is_multisite() ? 'Yes' : 'No',
                'wtsequentialordnum_version' => WT_SEQUENCIAL_ORDNUMBER_VERSION
            );
            // Write an action/hook here in webtoffe to recieve the data
            $resp = wp_remote_post('https://feedback.webtoffee.com/wp-json/wtseqordnum/v1/uninstall', array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'body' => $data,
                'cookies' => array()
                    )
            );

            wp_send_json_success();
        }

    }
    new WT_Sequentialordnum_Uninstall_Feedback();

endif;