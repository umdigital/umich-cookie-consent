<div class="wrap">
    <h2>University of Michigan: Cookie Consent</h2>
    <p></p>
    <form method="post" action="options.php">
        <?php settings_fields( 'umich-cc' ); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Cookie Consent Mode:</th>
                <td>
                    <input type="radio" id="umich_cc_options--mode-1" name="umich_cc_options[mode]" value="dev"<?php echo ($umCCOptions['mode'] == 'dev' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--mode-1">Development</label>

                    <input type="radio" id="umich_cc_options--mode-2" name="umich_cc_options[mode]" value="prod"<?php echo ($umCCOptions['mode'] == 'prod' ? ' checked="checked"' : null);?> />
                    <label for="umich_cc_options--mode-2">Production</label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row"><label for="umcc-domain">Domain:</label></th>
                <td>
                    <select id="umcc-domain" name="umich_cc_options[domain]" aria-describedby="umcc-domain-description">
                        <option value="">Autodetect</option>
                        <?php foreach( $umCCDomains as $domain ): ?>
                        <option value="<?=$domain;?>"<?=( $umCCOptions['domain'] == $domain ? ' selected="selected"' : null);?>><?=$domain;?></option>
                        <?php endforeach; ?>
                    </select>
                    <p id="umcc-domain-description" class="description">Current autodetect value is: <?=$umCCAutodetect;?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
