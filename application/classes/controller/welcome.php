<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$value = "This is the string to encrypt!";
		$this->request->output = 'Before: '.$value.'<br />';

		// enable mcrypt for encryption
		Encrypt::set('use_mcrypt', true);

		$value = Encrypt::encrypt($value);
		$this->request->output .= 'Encrypted: '.$value.'<br />';

		// disable mcrypt for decryption
		Encrypt::set('use_mcrypt', false);

		$value = Encrypt::decrypt($value);
		$this->request->output .= 'Decrypted: '.$value.'<br />';

		// we should still see a decrypted value due to autodetect

		$this->request->output .= '<hr />Hello from the the Welcome controller!';
	}

	public function action_404()
	{
		$this->request->output = '404';
	}
}
