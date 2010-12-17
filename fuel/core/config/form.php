<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel\App;

/*
|--------------------------------------------------------------------------
| Form Wrapper tags
|--------------------------------------------------------------------------
|
| These tags will wrap the different elements in the form.
|
| Example:
| 'form_wrapper_open']	= '<ul>';
| 'form_wrapper_close']	= '</ul>';
|
| 'input_wrapper_open']	= '<li>';
| 'input_wrapper_close']	= '</li>';
|
| 'label_wrapper_open']	= '<label for="%s">';
| 'label_wrapper_close']	= '</label>';
|
| 'required_location']	= 'after';
| 'required_tag']		= '<span class="required">*</span>';
|
| Would result in the following form:
| <form action="" method="post">
| <ul>
|     <li>
|         <label for="first_name">First Name</label>
|         <input type="text" name="first_name" id="first_name" value="" />
|     </li>
| </ul>
| </form>
*/

return array(
	'form_wrapper_open'		=> '<fieldset>',
	'form_wrapper_close'	=> '</fieldset>',

	'input_wrapper_open'	=> '<p>',
	'input_wrapper_close'	=> '</p>',

	'label_wrapper_open'	=> '<label for="%s">',
	'label_wrapper_close'	=> '</label>',

	'required_location'		=> 'after',
	'required_tag'		=> '<span class="required">*</span>',

	'forms' => array(
		'create_user' => array(
			'action'	=> 'users/create',
			'fields'	=> array(
				'id'	=> array(
					'type'		=> 'hidden',
					'value'		=> ''
				),
				'username'	 => array(
					'label'		=> 'Username',
					'type'		=> 'text',
					'size'		=> '40',
					'value'		=> ''
				),
				'first_name' => array(
					'label'		=> 'First Name',
					'type'		=> 'text',
					'size'		=> '40'
				),
				'last_name'	 => array(
					'label'		=> 'Last Name',
					'type'		=> 'text',
					'size'		=> '40',
					'value'		=> ''
				),
				'password'	 => array(
					'label'		=> 'Password',
					'type'		=> 'password',
					'size'		=> '40',
					'value'		=> ''
				),
				'public' => array(
					'type'		=> 'radio',
					'label'		=> 'Public?',
					'items'		=> array(
						array(
							'label'		=> 'Yes',
							'checked'	=> 'checked',
							'value'		=> '1',
						),
						array(
							'label'		=> 'No',
							'value'		=> '0',
						)
					)
				),
				'display_options' => array(
					'type'		=> 'checkbox',
					'label'		=> 'Display Options',
					'items'		=> array(
						array(
							'label'		=> 'Display Email',
							'checked'	=> 'checked',
							'value'		=> '1',
						),
						array(
							'label'		=> 'Display Real Name',
							'checked'	=> 'checked',
							'value'		=> '1',
						),
					)
				),
				'bio'	=> array(
					'label'		=> 'Bio',
					'type'		=> 'textarea',
					'rows'		=> '4',
					'cols'		=> '50',
					'value'		=> ''
				),
				'plan' => array(
					'type'		=> 'select',
					'label'		=> 'Plan',
					'selected'	=> '2',
					'options'	=> array(
						'1' => 'Basic',
						'2' => 'Standard',
						'3' => 'Advanced'
					)
				),
				'action'		 => array(
					'label'		=> '',
					'type'		=> 'submit',
					'value'		=> 'Create'
				)
			)
		) // end create_user
	
	) // end forms
);

/* End of file form.php */