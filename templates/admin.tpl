<div class="wrap">
    <h2>University of Michigan: Cookie Consent</h2>
    <p></p>
    <script type="text/javascript">
        if( typeof jQuery !== 'undefined' ) {
            (function($){
                $(document).ready(function(){
                    $('input[name="umich_cc_options[custom]"]').on('change', function(){
                        let isCustom = parseInt( $('input[name="umich_cc_options[custom]"]:checked').val() ) ? true : false;

                        if( isCustom ) {
                            $('input[name="umich_cc_options[custom]"]').closest('table').find('tr.custom-option').show();
                        }
                        else {
                            $('input[name="umich_cc_options[custom]"]').closest('table').find('tr.custom-option').hide();
                        }
                    }).trigger('change');
                });
            }(jQuery));
        }
    </script>
    <form method="post" action="options.php">
        <?php settings_fields( 'umich-cc' ); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row" aria-describedby="umcc-cc_options--mode">Cookie Consent Mode:</th>
                <td>
                    <input type="radio" id="umich_cc_options--mode-1" name="umich_cc_options[mode]" value="dev"<?php echo ($umCCOptions['mode'] == 'dev' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--mode-1">Development</label>

                    <input type="radio" id="umich_cc_options--mode-2" name="umich_cc_options[mode]" value="prod"<?php echo ($umCCOptions['mode'] == 'prod' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--mode-2">Production</label>

                    <p id="umcc-cc_options--mode" class="description">Development mode will restrict to logged in users.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="umcc-privacy-url">Privacy Policy URL</label></th>
                <td>
                    <input type="text" id="umcc-privacy-url" name="umich_cc_options[privacy_url]" value="<?php echo $umCCOptions['privacy_url'];?>" placeholder="/privacy/" />

                    <?php if( $umCCDefaultPrivacyUrl ): ?>
                    <p id="umcc-cc_options--privacy-url" class="description">If you do not have your own then /privacy/ will be redirected to <?php echo $umCCDefaultPrivacyUrl; ?></p>
                    <?php else: ?>
                    <p id="umcc-cc_options--privacy-url" class="description">If you do not have your own then /privacy/ will result in a page not found error.</p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="umcc-google-analytics-id" aria-describedby="umcc-cc_options--google-analytics-id">Google Analytics ID</label></th>
                <td>
                    <input type="text" id="umcc-google-analytics-id" name="umich_cc_options[google_analytics_id]" value="<?php echo $umCCOptions['google_analytics_id'];?>" placeholder="G-XXXXXXXXXX" />

                    <p id="umcc-cc_options--google-analytics-id" class="description">This will add the necessary default google analytics / tag manager code in a compliant manner.  You won't need to use any other method to add the code to your site.</p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" aria-describedby="umcc-cc_options--custom">Custom Manager:</th>
                <td>
                    <?php if( (!function_exists( 'hash' ) || !in_array( 'sha1', hash_algos() )) && !function_exists( 'sha1' ) ): ?>
                    <h3 style="margin-top: 0;">NOTICE!</h3>
                    <p>Manager customizations are unavailable due to missing required functions 'hash' or 'sha1'.  Please enabled one of these functions in order to use these features.<p>
                    <input type="radio" id="umich_cc_options--custom-2" name="umich_cc_options[custom]" value="0" style="display: none;" />
                    <?php else: ?>
                    <input type="radio" id="umich_cc_options--custom-1" name="umich_cc_options[custom]" value="1"<?php echo ($umCCOptions['custom'] == '1' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--custom-1">Enabled</label>

                    <input type="radio" id="umich_cc_options--custom-2" name="umich_cc_options[custom]" value="0"<?php echo ($umCCOptions['custom'] != '1' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--custom-2">Disabled</label>

                    <p id="umcc-cc_options--custom" class="description">Enabling will not share settings with other sites on the same top level domain as well as provide more customization options.</p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr valign="top" class="custom-option" style="display: none;">
                <th scope="row" aria-describedby="umcc-cc_options--always-show">Always Show:</th>
                <td>
                    <input type="radio" id="umich_cc_options--always-show-1" name="umich_cc_options[always_show]" value="1"<?php echo ($umCCOptions['always_show'] == '1' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--always-show-1">Enabled</label>

                    <input type="radio" id="umich_cc_options--always-show-2" name="umich_cc_options[always_show]" value="0"<?php echo ($umCCOptions['always_show'] != '1' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--always-show-2">Disabled</label>

                    <p id="umcc-cc_options--always-show" class="description">Enabled will show the banner to any user reguardless of their location.  Disabled will only show to countries with strict legal requirements (e.g. European Union countries).</p>
                </td>
            </tr>

            <tr valign="top" class="custom-option" style="display: none;">
                <th scope="row"><label for="umcc-domain" aria-describedby="umcc-cc_options--domain">Parent Domain:</label></th>
                <td>
                    <select id="umcc-domain" name="umich_cc_options[domain]" aria-describedby="umcc-domain-description">
                        <option value="">Select a Subdomain</option>
                        <?php foreach( $umCCDomains as $domain ): ?>
                        <option value="<?=$domain;?>"<?=( $umCCOptions['domain'] == $domain ? ' selected="selected"' : null);?>><?=$domain;?></option>
                        <?php endforeach; ?>
                    </select>
                    <p id="umcc-cc_options--domain" class="description">If you want privacy preference to be shared amongst sub-domains select the parent domain to share from.  E.g. vpcomm.umich.edu will share with publicaffairs.vpcomm.umich.edu</p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
