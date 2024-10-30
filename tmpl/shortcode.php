<?php if (!defined('ABSPATH')) exit; ?>
<?php include_once("common/top-menu.php"); ?>
<?php $nonce_url = wp_nonce_url( "./admin.php?page=reai_shortcode", 'shortcode-nonce' ); ?>

<div id="rai-shortcodes">
    <h1>Shortcodes</h1>

    <p>Use shortcode to implement the AI Chatbot in a page, not as a floating widget. Fill the following form to generate shortcode code:</p>
    <form action="<?php echo esc_attr($nonce_url); ?>" method="post">
        
        <p>
            <h2>Chatbot ID:</h2>
            <textarea cols="70" rows="2" id="reai_chatbot_id" name="reai_chatbot_id"><?php echo esc_textarea(stripslashes($chatbot_id)) ?></textarea>
        </p>
        <p>
            <h2>Size:</h2>
            <div>
                Width: 
                <input type="number" id="reai_width" name="reai_width" placeholder="360" value="<?php echo esc_attr($reai_width); ?>" />
                <select name="reai_width_type">
                    <option value="px">px</option>
                    <option <?php echo $reai_width_type==="%" ? 'selected' : '';?> value="%">%</option>
                </select>
            </div>
            <div>
                Height: 
                <input type="number" id="reai_height" name="reai_height" placeholder="630" value="<?php echo esc_attr($reai_height); ?>" />
                <select name="reai_height_type">
                    <option value="px">px</option>
                    <option <?php echo $reai_height_type==="%" ? 'selected' : '';?> value="%">%</option>
                </select>
            </div>        
        </p>
        <p>
            <h2>Alignment:</h2>
            <div>
                <select name="reai_align">
                    <option value="left">Left</option>
                    <option <?php echo $reai_align==="center" ? 'selected' : '';?> value="center">Center</option>
                    <option <?php echo $reai_align==="right" ? 'selected' : '';?> value="right">Right</option>
                </select>
            </div>    
        </p>
        <p>
            <input type="submit" value="Show shortcode" name="reai_save" class="button button-primary" />
        </p>
    </form>

    <?php if(!empty($reai_shortcode)) { ?>
    <p>
        <h2>Shortcode:</h2>
        <code id="shortcode"><?php echo esc_html($reai_shortcode); ?></code>
        <div><button class="button button-secondary" id="copy-shortcode">Copy to Clipboard</button><span style="display:none" id="copy-success" class='dashicons dashicons-yes'></span></div>
    </p>
    <?php } ?>

    <?php if(!empty($chat_preview)) { ?>
    <p>
        <h2>Preview:</h2>
        <div><?php echo wp_kses($chat_preview); ?></div>
    </p>    
    <?php } ?>
</div>