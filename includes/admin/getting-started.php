<?php
/**
 * Getting Started
 *
 * @package WP_EASY_CONTACT
 * @since WPAS 5.3
 */
if (!defined('ABSPATH')) exit;
add_action('wp_easy_contact_getting_started', 'wp_easy_contact_getting_started');
/**
 * Display getting started information
 * @since WPAS 5.3
 *
 * @return html
 */
function wp_easy_contact_getting_started() {
	global $title;
	list($display_version) = explode('-', WP_EASY_CONTACT_VERSION);
?>
<style>
div.comp-feature {
    font-weight: 400;
    font-size:20px;
}
.ent-com {
    display: none;
}
.green{
color: #008000;
font-size: 30px;
}
#nav-compare:before{
    content: "\f179";
}
#emd-about .nav-tab-wrapper a:before{
    position: relative;
    box-sizing: content-box;
padding: 0px 3px;
color: #4682b4;
    width: 20px;
    height: 20px;
    overflow: hidden;
    white-space: nowrap;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
font-family: dashicons;
}
#nav-getting-started:before{
content: "\f102";
}
#nav-whats-new:before{
content: "\f348";
}
#nav-resources:before{
content: "\f118";
}
#emd-about .embed-container { 
	position: relative; 
	padding-bottom: 56.25%;
	height: 0;
	overflow: hidden;
	max-width: 100%;
	height: auto;
	} 

#emd-about .embed-container iframe,
#emd-about .embed-container object,
#emd-about .embed-container embed { 
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	}
#emd-about ul li:before{
    content: "\f522";
    font-family: dashicons;
    font-size:25px;
 }
#gallery {
	margin: auto;
}
#gallery .gallery-item {
	float: left;
	margin-top: 10px;
	margin-right: 10px;
	text-align: center;
	width: 48%;
        cursor:pointer;
}
#gallery img {
	border: 2px solid #cfcfcf; 
height: 405px;  
}
#gallery .gallery-caption {
	margin-left: 0;
}
#emd-about .top{
text-decoration:none;
}
#emd-about .toc{
    background-color: #fff;
    padding: 25px;
    border: 1px solid #add8e6;
    border-radius: 8px;
}
#emd-about h3,
#emd-about h2{
    margin-top: 0px;
    margin-right: 0px;
    margin-bottom: 0.6em;
    margin-left: 0px;
}
#emd-about p,
#emd-about .emd-section li{
font-size:18px
}
#emd-about a.top:after{
content: "\f342";
    font-family: dashicons;
    font-size:25px;
text-decoration:none;
}
#emd-about .toc a,
#emd-about a.top{
vertical-align: top;
}
#emd-about li{
list-style: none;
}
#emd-about .quote{
    background: #fff;
    border-left: 4px solid #088cf9;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    margin-top: 25px;
    padding: 1px 12px;
}
#emd-about .tooltip{
    display: inline;
    position: relative;
}
#emd-about .tooltip:hover:after{
    background: #333;
    background: rgba(0,0,0,.8);
    border-radius: 5px;
    bottom: 26px;
    color: #fff;
    content: 'Click to enlarge';
    left: 20%;
    padding: 5px 15px;
    position: absolute;
    z-index: 98;
    width: 220px;
}
</style>

<?php add_thickbox(); ?>
<div id="emd-about" class="wrap about-wrap">
<div id="emd-header" style="padding:10px 0" class="wp-clearfix">
<div style="float:right"><img src="https://emd-plugins.s3.amazonaws.com/wp-contact-logo-300x45.png"></div>
<div style="margin: .2em 200px 0 0;padding: 0;color: #32373c;line-height: 1.2em;font-size: 2.8em;font-weight: 400;">
<?php printf(__('Welcome to WP Easy Contact Community %s', 'wp-easy-contact') , $display_version); ?>
</div>

<p class="about-text">
<?php printf(__("Provides easy to use contact management", 'wp-easy-contact') , $display_version); ?>
</p>

<?php
	$tabs['getting-started'] = __('Getting Started', 'wp-easy-contact');
	$tabs['whats-new'] = __('What\'s New', 'wp-easy-contact');
	$tabs['resources'] = __('Resources', 'wp-easy-contact');
	$tabs['compare'] = __('Compare Editions', 'wp-easy-contact');
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'getting-started';
	echo '<h2 class="nav-tab-wrapper wp-clearfix">';
	foreach ($tabs as $ktab => $mytab) {
		$tab_url[$ktab] = esc_url(add_query_arg(array(
			'tab' => $ktab
		)));
		$active = "";
		if ($active_tab == $ktab) {
			$active = "nav-tab-active";
		}
		echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . $active . '" id="nav-' . $ktab . '">' . $mytab . '</a>';
	}
	echo '</h2>';
	echo '<div class="tab-content" id="tab-getting-started"';
	if ("getting-started" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<div style="height:25px" id="rtop"></div><div class="toc"><h3 style="color:#0073AA;text-align:left;">Quickstart</h3><ul><li><a href="#gs-sec-122">WP Easy Contact Community Introduction</a></li>
<li><a href="#gs-sec-128">Upgrade to WP Easy Contact Pro - Best Contact Management System for WordPress</a></li>
<li><a href="#gs-sec-124">EMD CSV Import Export Extension helps you get your data in and out of WordPress quickly, saving you ton of time</a></li>
<li><a href="#gs-sec-123">EMD Advanced Filters and Columns Extension for finding what's important faster</a></li>
<li><a href="#gs-sec-127">EMD MailChimp Extension for building email list through WP Easy Contact</a></li>
<li><a href="#gs-sec-129">Incoming Email WordPress Plugin - Create contacts from emails</a></li>
</ul></div><div class="quote">
<p class="about-description">The secret of getting ahead is getting started - Mark Twain</p>
</div>
<div class="getting-started emd-section changelog getting-started getting-started-122" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-122"></div><h2>WP Easy Contact Community Introduction</h2><div class="emd-yt" data-youtube-id="wXaxzip-92M" data-ratio="16:9">loading...</div><div class="sec-desc"><p>Watch WP Easy Contact Community introduction video to learn about the plugin features and configuration.</p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="getting-started emd-section changelog getting-started getting-started-128" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-128"></div><h2>Upgrade to WP Easy Contact Pro - Best Contact Management System for WordPress</h2><div id="gallery" class="wp-clearfix"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-128" href="https://emdsnapshots.s3.amazonaws.com/montage_wpeasy_contact_pro.jpg"><img src="https://emdsnapshots.s3.amazonaws.com/montage_wpeasy_contact_pro.jpg"></a></div></div><div class="sec-desc"><p>WP Easy Contact Pro --> easy to use and powerful contact management system for WordPress with best in class features.</p><div style="margin:25px"><a href="https://emdplugins.com/plugins/wp-easy-contact-professional/?pk_campaign=wpec-pro-buybtn&pk_kwd=wp-easy-contact-resources"><img src="https://emd-plugins.s3.amazonaws.com/button_buy-now.png"></a></div></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="getting-started emd-section changelog getting-started getting-started-124" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-124"></div><h2>EMD CSV Import Export Extension helps you get your data in and out of WordPress quickly, saving you ton of time</h2><div class="emd-yt" data-youtube-id="tJDQbU3jS0c" data-ratio="16:9">loading...</div><div class="sec-desc"><p><b>This feature is included in WP Easy Contact Pro edition.</b></p>
<p>EMD CSV Import Export Extension helps bulk import, export, update entries from/to CSV files. You can also reset(delete) all data and start over again without modifying database. The export feature is also great for backups and archiving old or obsolete data.</p>
<p><a href="https://emdplugins.com/plugins/emd-csv-import-export-extension/?pk_campaign=emdimpexp-buybtn&pk_kwd=wp-easy-contact-resources"><img src="https://emd-plugins.s3.amazonaws.com/button_buy-now.png"></a></p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="getting-started emd-section changelog getting-started getting-started-123" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-123"></div><h2>EMD Advanced Filters and Columns Extension for finding what's important faster</h2><div class="emd-yt" data-youtube-id="JDIHIibWyR0" data-ratio="16:9">loading...</div><div class="sec-desc"><p><b>This feature is included in WP Easy Contact Pro edition.</b></p>
<p>EMD Advanced Filters and Columns Extension for WP Easy Contact Community edition helps you:</p><ul><li>Filter entries quickly to find what you're looking for</li><li>Save your frequently used filters so you do not need to create them again</li><li>Sort entry columns to see what's important faster</li><li>Change the display order of columns </li>
<li>Enable or disable columns for better and cleaner look </li><li>Export search results to PDF or CSV for custom reporting</li></ul><div style="margin:25px"><a href="https://emdplugins.com/plugins/emd-advanced-filters-and-columns-extension/?pk_campaign=emd-afc-buybtn&pk_kwd=wp-easy-contact-resources"><img src="https://emd-plugins.s3.amazonaws.com/button_buy-now.png"></a></div></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="getting-started emd-section changelog getting-started getting-started-127" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-127"></div><h2>EMD MailChimp Extension for building email list through WP Easy Contact</h2><div class="emd-yt" data-youtube-id="Oi_c-0W1Sdo" data-ratio="16:9">loading...</div><div class="sec-desc"><p>EMD MailChimp Extension helps you build MailChimp email list based on the contact information collected through WP Easy Contact Community form.</p><div style="margin:25px"><a href="https://emdplugins.com/plugins/emd-mailchimp-extension/?pk_campaign=emd-mailchimp-buybtn&pk_kwd=wp-easy-contact-resources"><img src="https://emd-plugins.s3.amazonaws.com/button_buy-now.png"></a></div></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="getting-started emd-section changelog getting-started getting-started-129" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-129"></div><h2>Incoming Email WordPress Plugin - Create contacts from emails</h2><div class="emd-yt" data-youtube-id="FJeMhseoZJQ" data-ratio="16:9">loading...</div><div class="sec-desc"><p>Incoming Email WordPress Plugin allows to create contact records from emails opening up another channel to grow your list or generate leads.</p>

<p><a href="href="https://wpappstudio.com/connections/incoming-email-connection/?pk_campaign=wpasincemail-buybtn&pk_kwd=simpro-resources"><img src="https://emd-plugins.s3.amazonaws.com/button_buy-now.png"></a></p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px">

<?php echo '</div>';
	echo '<div class="tab-content" id="tab-whats-new"';
	if ("whats-new" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<p class="about-description">WP Easy Contact Community V3.0.0 offers many new features, bug fixes and improvements.</p>


<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:125px;border: 2px solid #fff;border-radius:4px">3.0.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-213" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Configured to work with EMD Advanced Filters and Columns Extension for finding what’s important faster</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-212" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Configured to work with EMD CSV Import Export Extension for bulk import/export</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-211" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Configured to work with EMD MailChimp extension</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-210" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added a getting started page for plugin introduction, tips and resources</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-209" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added topic taxonomy to contact form</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-152" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Template System</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<?php echo '</div>';
	echo '<div class="tab-content" id="tab-resources"';
	if ("resources" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<div style="height:25px" id="ptop"></div><div class="toc"><h3 style="color:#0073AA;text-align:left;">Upgrade your game for better results</h3><ul><li><a href="#gs-sec-121">Extensive documentation is available</a></li>
<li><a href="#gs-sec-126">How to customize WP Easy Contact Community</a></li>
<li><a href="#gs-sec-125">How to resolve theme related issues</a></li>
</ul></div><div class="emd-section changelog resources resources-121" style="margin:0"><div style="height:40px" id="gs-sec-121"></div><h2>Extensive documentation is available</h2><div id="gallery" class="wp-clearfix"></div><div class="sec-desc"><a href="https://docs.emdplugins.com/docs/wp-easy-contact-community">WP Easy Contact Community Documentation</a></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="emd-section changelog resources resources-126" style="margin:0"><div style="height:40px" id="gs-sec-126"></div><h2>How to customize WP Easy Contact Community</h2><div class="emd-yt" data-youtube-id="4wcFcIfHhPA" data-ratio="16:9">loading...</div><div class="sec-desc"><p><strong><span class="dashicons dashicons-arrow-up-alt"></span> Watch the customization video to familiarize yourself with the customization options. </strong>. The video shows one of our plugins as an example. The concepts are the same and all our plugins have the same settings.</p>
<p>WP Easy Contact Community is designed and developed using <a href="https://wpappstudio.com">WP App Studio (WPAS) Professional WordPress Development platform</a>. All WPAS plugins come with extensive customization options from plugin settings without changing theme template files. Some of the customization options are listed below:</p>
<ul>
	<li>Enable or disable all fields, taxonomies and relationships from backend and/or frontend</li>
        <li>Use the default EMD or theme templating system</li>
	<li>Set slug of any entity and/or archive base slug</li>
	<li>Set the page template of any entity, taxonomy and/or archive page to sidebar on left, sidebar on right or no sidebar (full width)</li>
	<li>Hide the previous and next post links on the frontend for single posts</li>
	<li>Hide the page navigation links on the frontend for archive posts</li>
	<li>Display or hide any custom field</li>
	<li>Display any sidebar widget on plugin pages using EMD Widget Area</li>
	<li>Set custom CSS rules for all plugin pages including plugin shortcodes</li>
</ul>
<div class="quote">
<p>If your customization needs are more complex, you’re unfamiliar with code/templates and resolving potential conflicts, we strongly suggest you to <a href="https://emdplugins.com/open-a-support-ticket/?pk_campaign=wp-easy-contact-hireme-custom&ticket_topic=pre-sales-questions">hire us</a>, we will get your site up and running in no time.
</p>
</div></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div class="emd-section changelog resources resources-125" style="margin:0"><div style="height:40px" id="gs-sec-125"></div><h2>How to resolve theme related issues</h2><div id="gallery" class="wp-clearfix"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-125" href="https://emdsnapshots.s3.amazonaws.com/emd_templating_system.png"><img src="https://emdsnapshots.s3.amazonaws.com/emd_templating_system.png"></a></div></div><div class="sec-desc"><p>If your theme is not coded based on WordPress theme coding standards, does have an unorthodox markup or its style.css is messing up how WP Easy Contact Community pages look and feel, you will see some unusual changes on your site such as sidebars not getting displayed where they are supposed to or random text getting displayed on headers etc. after plugin activation.</p>
<p>The good news is WP Easy Contact Community plugin is designed to minimize theme related issues by providing two distinct templating systems:</p>
<ul>
<li>The EMD templating system is the default templating system where the plugin uses its own templates for plugin pages.</li>
<li>The theme templating system where WP Easy Contact Community uses theme templates for plugin pages.</li>
</ul>
<p>The EMD templating system is the recommended option. If the EMD templating system does not work for you, you need to check "Disable EMD Templating System" option at Settings > Tools tab and switch to theme based templating system.</p>
<p>Please keep in mind that when you disable EMD templating system, you loose the flexibility of modifying plugin pages without changing theme template files.</p>
<p>If none of the provided options works for you, you may still fix theme related conflicts following the steps in <a href="https://docs.emdplugins.com/docs/wp-easy-contact-community">WP Easy Contact Community Documentation - Resolving theme related conflicts section.</a></p>

<div class="quote">
<p>If you’re unfamiliar with code/templates and resolving potential conflicts, <a href="https://emdplugins.com/open-a-support-ticket/?pk_campaign=raq-hireme&ticket_topic=pre-sales-questions"> do yourself a favor and hire us</a>. Sometimes the cost of hiring someone else to fix things is far less than doing it yourself. We will get your site up and running in no time.</p>
</div></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px">
<?php echo '</div>'; ?>
<?php echo '<div class="tab-content" id="tab-compare"';
	if ("compare" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<div class="sec-desc"><p>WP Easy Contact Pro --&gt; easy to use and powerful contact management system for WordPress with best in class features.</p><div style="margin:25px"><a href="https://emdplugins.com/plugins/wp-easy-contact-professional/?pk_campaign=wpec-pro-buybtn&amp;pk_kwd=wp-easy-contact-compare"><img src="https://emd-plugins.s3.amazonaws.com/button_buy-now.png"></a></div></div>

<table class="widefat striped table emd-table table-no-bordered" data-classes="table table-no-bordered" data-search="true" data-toggle="table" id="comp-results">
                <thead><tr><th class="comp-feature" style="text-align: left; " data-field="comp-feature" tabindex="0"><div class="th-inner sortable both asc"><strong>Feature</strong></div><div class="fht-cell"></div></th><th class="com-com" style="text-align: center; " data-field="com-com" tabindex="0"><div class="th-inner sortable both"><strong>Community</strong></div><div class="fht-cell"></div></th><th class="pro-com" style="text-align: center; " data-field="pro-com" tabindex="0"><div class="th-inner sortable both"><strong>Pro</strong></div><div class="fht-cell"></div></th><th class="ent-com" style="text-align: center; " data-field="ent-com" tabindex="0"><div class="th-inner sortable both"><strong>Enterprise</strong></div><div class="fht-cell"></div></th></tr></thead>
                <tbody><tr data-index="0"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Advanced contact list management</div>
        <div class="comp-feature-desc">Filter, sort and dynamically track contacts based on custom criteria</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="1"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">AJAX form submission</div>
        <div class="comp-feature-desc">Fast form submissions without additional page loads</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="2"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Categorize and tag contacts</div>
        <div class="comp-feature-desc">Categorize contact on custom tags, topics, country and state</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="3"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Concact form with social block</div>
        <div class="comp-feature-desc">Social block supports 9 major networks</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="4"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Connect contacts to contacts</div>
        <div class="comp-feature-desc">Create contact relationships including type, start/end dates and definitions</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="5"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Contact form with advanced components</div>
        <div class="comp-feature-desc">Bootsrap based components with advanced search</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="6"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Contact form with map</div>
        <div class="comp-feature-desc">Fully customizable location map</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="7"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Contact manager user role</div>
        <div class="comp-feature-desc">WordPress user role design to manage contacts</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="8"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Customizable contact form</div>
        <div class="comp-feature-desc">Easily customize your form from plugin settings</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="9"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Customizable terms and conditions field</div>
        <div class="comp-feature-desc">Option to ask contact's aggrement TOC before accepting submission</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="10"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">eMD Incoming email add-on support</div>
        <div class="comp-feature-desc">Convert incoming emails to contacts</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="11"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">eMD MailChimp add-on support</div>
        <div class="comp-feature-desc">Grow your MailChimp email list with advanced features</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="12"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">File uploads</div>
        <div class="comp-feature-desc">Allow contacts to upload files and media with their form submission</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="13"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Get insight on contacts</div>
        <div class="comp-feature-desc">Contact dashboard with 4 charts to profile your contacts</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="14"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Import/export/update contacts from/to CSV</div>
        <div class="comp-feature-desc">Link your contacts from/to external systems such CRM using CSV files</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="15"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Instant notification to adminitrator user</div>
        <div class="comp-feature-desc">Respond to contacts quickly with fully customizable notifications</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="16"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Instant notification to contact</div>
        <div class="comp-feature-desc">Notify contacts instantly upon form submission with fully customizable emails</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="17"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Powerful contact task management</div>
        <div class="comp-feature-desc">Track your work and close deals with poweful task management</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="18"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Responsive mobile friendly contact form</div>
        <div class="comp-feature-desc">Works and displays beautifully on desktops, tablets and phones</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr><tr data-index="19"><td class="comp-feature" style="text-align: left; ">
        <div class="comp-feature">Store unlimited contacts</div>
        <div class="comp-feature-desc">View all your contact in one central location to streamline your workflow</div>
    </td><td class="com-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="pro-com" style="text-align: center; "><i class="dashicons dashicons-yes green"></i></td><td class="ent-com" style="text-align: center; "><i class="dashicons dashicons-no-alt"></i></td></tr></tbody>
</table>
<?php echo '</div>'; ?>
<?php echo '</div>';
}
