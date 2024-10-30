<?php if (!defined('ABSPATH')) exit; ?>
<?php include_once("common/top-menu.php"); ?>
<?php $nonce_url = wp_nonce_url( "./admin.php?page=reai_home", 'home-nonce' ); ?>

<h1>ResolveAI</h1>
<h2>Custom AI Chatbot with your own data, personality & branding.</h2>

<p>
    Visit ResolveAI website to learn more: <a href='<?php echo esc_url($this->websiteUrl);?>/?utm_source=plugin&utm_medium=wordpress&utm_campaign=<?php echo esc_attr($this->hostname);?>' target='_blank'><?php echo esc_url($this->websiteUrl);?> <span class="dashicons dashicons-external"></span></a>.
</p>

<p>
    <h3>How to embed your AI Chatbot:</h3>
	<ol>
		<li>Login to your <a href='<?php echo esc_url($this->appUrl);?>/?utm_source=plugin&utm_medium=wordpress&utm_campaign=<?php echo esc_attr($this->hostname);?>' target='_blank'>ResolveAI Dashboard <span class="dashicons dashicons-external"></span></a>. 
        Don't have an account? <a href='<?php echo esc_url($this->appUrl);?>/signup/?utm_source=plugin&utm_medium=wordpress&utm_campaign=<?php echo esc_attr($this->hostname);?>' target='_blank'>Create account here <span class="dashicons dashicons-external"></span></a>.</li>
		<li>Go to "Chatbots" and click on your Chatbot.</li>
        <li>Go to "Publish Chatbot" and click on "Wordpress".</li>
		<li>Copy the Chatbot ID by clicking on the "Copy Chatbot ID to clipboard" button:<br /><img src="<?php echo esc_url(plugins_url( '../images/publish.png', __FILE__ ));?>" /></li>
        <li>Copy the <u>Chatbot id</u> and paste here:
            <form action="<?php echo esc_attr($nonce_url); ?>" method="post">
            <textarea cols="70" rows="2" id="reai_chatbot_id" name="reai_chatbot_id"><?php echo esc_textarea(stripslashes(get_option('reai_chatbot_id'))) ?></textarea>
            <br/><input type="submit" value="Save" name="reai_save" class="button button-primary" />
            <?php if($saved) echo "<span class='dashicons dashicons-yes'></span>"; ?>
            </form>
        </li>
        <li>Embed the AI chat as a <a href="?page=reai_widget">Floating widget</a> and/or using <a href="?page=reai_shortcode">Shortcode</a>.</li>
	</ol>
</p>
<?php
/*
$chatbot_id = get_option('reai_chatbot_id');
if(!empty($chatbot_id)) {
    echo $this->get_widget_code($chatbot_id, false);
}
*/
?>