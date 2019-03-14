<?php
/**
 * Admin Environment
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


interface TWPF_Interface_Admin_Page
{
	public function load_page();
	public function handle_actions();
	public function print_scripts();
	public function render_page();
}
