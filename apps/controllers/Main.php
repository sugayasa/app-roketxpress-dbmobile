<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_controller {
	
	public function __construct(){
        parent::__construct();
		$this->load->library('session');
    }
	
	public function index(){
		header('HTTP/1.0 403 Forbidden');
		echo 'Forbidden!';
		die();
	}

}