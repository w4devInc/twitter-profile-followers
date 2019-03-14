<?php
/**
 * Logs
 * @package WordPress
 * @subpackage  Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


if (! defined('ABSPATH')) {
	die('Accessing directly to this file is not allowed');
}

class TWPF_Admin_Page_Logs implements TWPF_Interface_Admin_Page
{
	public function __construct()
	{
		add_action('admin_menu' 								, [$this, 'admin_menu'], 200);
		add_action('wp_ajax_twpf_clear_logs'					, [$this, 'clear_logs_ajax']);
		add_action('wp_ajax_twpf_logs_template'					, [$this, 'logs_template_ajax']);
		add_action('twpf_daily_cron'							, 'TWPF_Logger::clear_logs');
	}
	public function handle_actions()
	{
	}

	public function clear_logs_ajax()
	{
		TWPF_Logger::clear_logs();
		TWPF_Utils::ajax_ok("Logs cleaned");
	}

	public function logs_template_ajax()
	{
		if (file_exists(TWPF_Logger::log_file())) {
			TWPF_Utils::ajax_ok($this->log_template());
		} else {
			TWPF_Utils::ajax_error(__('No logs available :)', 'twpf'));
		}
	}

	public function load_page()
	{
		if (! wp_next_scheduled('twpf_daily_cron')) {
			wp_schedule_event(time() + 2, 'daily', 'twpf_daily_cron');
		}
		do_action('twpf/admin_page/logs/load');
	}

	public function render_page()
	{
		?><style>
			#twpf_logs_wrap{ max-height:340px; overflow:hidden; overflow-y:scroll;}
			#twpf_logs_wrap ul li{ padding:8px 5px 8px 120px; margin:0; font-size:12px; border-bottom:1px solid #e5e5e5; position:relative; }
			#twpf_logs_wrap > ul > li > time{ position:absolute; top: auto; left:10px; }
			#twpf_logs_wrap ul ul > li{ padding:5px;  border-bottom:none; }
			#twpf_logs_wrap ul ul > li:before{ content:"- "}
			@media (min-width:480px){
				.twpf-box-title .wff_ajax_action_btn{float:right;}
			}
			@media (max-width:480px){
				.twpf-box-title span{display:block;}
				.twpf-box-title .wff_ajax_action_btn{margin-top:20px;}
			}
		</style>
		<div class="wrap twpf-wrap">
			<h1><?php _e('Logs', 'twpf'); ?></h1><br>
			<div class="twpf-admin-content">
				<div class="twpf-box">
					<div class="twpf-box-title">
	                    <span><?php _e('Logs refreshes automatically.', 'twpf'); ?></span>
                    	<a class="button wff_ajax_action_btn" data-target="#twpf_logs_wrap" data-url="<?php echo admin_url('admin-ajax.php?action=twpf_clear_logs'); ?>" data-action="twpf_clear_logs"><?php _e('Clear logs', 'twpf'); ?></a>
                    </div>
					<div class="twpf-box-content">
                        <div id="twpf_logs_wrap"><?php
                        if (file_exists(TWPF_Logger::log_file())) {
                            echo $this->log_template();
                        } else {
                            echo '<div class="_error"><p>'. __('No logs available :)', 'twpf') .'</p></div>';
                        }
                    ?></div>
                </div>
			</div>
		</div>

		<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				setTimeout(refreshLog, 5000);
				$(window).on('twpf_clear_logs/done', function(obj, r){
					if('ok' == r.status){
						$('#twpf_logs_wrap').html('<div class="_ok"><p><?php _e('logs cleared', 'twpf'); ?></p></div>');
					}
				});
			});
			function refreshLog(){
				$('.twpf_admin_widget h2').next('.twpf_desc').addClass('ld');
				$.post(ajaxurl + '?action=twpf_logs_template', function(r){
					if(r.status == 'ok'){
						$('#twpf_logs_wrap').html(r.html);
						setTimeout(refreshLog, 5000);
					}
					else if (r.status == 'error'){
						$('#twpf_logs_wrap').html('<div class="_error"><p>' + r.html + '</p></div>');
						setTimeout(refreshLog, 20000);
					}
					$('.twpf_admin_widget h2').next('.twpf_desc').removeClass('ld');
				});
			}
		})(jQuery);
		</script>
		<?php
	}

	public function log_template()
	{
		$buff = '';
		$lines = file(TWPF_Logger::log_file());

		if(! empty($lines))
		{
			$lines = array_reverse($lines);

			$buff .= '<ul>';
			foreach($lines as $line)
			{
				$date = substr($line,1, 19);
				$line = substr($line, 19 + 3);
				$line = maybe_unserialize(trim($line));

				if (is_array($line)) {
					$line = implode('</li><li>', $line);

					$buff .= '<li><ul>';
					$buff .= sprintf('<li>%s</li>', $line);
					$buff .= '</ul></li>';
				} else {
					$time = strtotime($date);
					$curr_time = current_time('timestamp');
					$date_str = date('d/M H:i A', $time);

					if ($time > $curr_time - HOUR_IN_SECONDS) {
						$buff .= sprintf('<li><time title="%s">'.__('%s ago', 'twpf').'</time><span>%s</span></li>', $date_str, human_time_diff($time, $curr_time), $line);
					} else {
						$buff .= sprintf('<li><time title="%s">%s</time><span>%s</span></li>', $date_str, $date_str, $line);
					}
				}
			}
			$buff .= '</ul>';
		}

		return $buff;
	}

	public function admin_menu()
	{
		// access capability
		$access_cap = apply_filters('twpf/access_cap/logs', 'manage_options');

		// register menu
		$admin_page = add_submenu_page(
			TWPF_SLUG,
			sprintf('%s - %s', __('Logs', 'twpf'), __(' Twitter Profile Followers', 'twpf')),
			__('Logs', 'twpf'),
			$access_cap,
			'twpf-logs',
			[$this, 'render_page']
		);

		add_action("admin_print_styles-{$admin_page}"	, [$this, 'print_scripts']);
		add_action("load-{$admin_page}"					, [$this, 'load_page']);
		add_action("load-{$admin_page}"					, [$this, 'handle_actions']);
	}

	public function print_scripts()
	{
		wp_localize_script('twpf_admin', 'twpf', [
			'apiUrl' 		=> rest_url('twpf/v2/'),
			'logsUrl'		=> admin_url('admin.php?page=twpf-logs')
		]);

		wp_enqueue_style(['twpf_admin']);
		wp_enqueue_script(['twpf_admin']);
	}
}
