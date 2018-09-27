<?php
/**
 * Decision box field
 *
 * @package EverestForms\Fields
 * @since   1.3.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Decision Class.
 */
class EVF_Field_Decision extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Decision Box', 'everest-forms' );
		$this->type     = 'decision';
		$this->icon     = 'evf-icon evf-icon-decision';
		$this->order    = 25;
		$this->group    = 'advanced';
		$this->settings = array(
			'basic-options' => array(
				'field_options' => array(
					'label',
					'meta',
					'description',
					'required',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'default_value',
					'css',
				),
			),
		);

		parent::__construct();
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @param array $field
	 */
	public function field_preview( $field ) {

		// Decision.
		$this->field_preview_option( 'decision', $field );

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @param array $field
	 * @param array $field_atts
	 * @param array $form_data
	 */
	public function field_display( $field, $field_atts, $form_data ) {}

	/**
	 * Formats and sanitizes field.
	 *
	 * @param int    $field_id
	 * @param array  $field_submit
	 * @param array  $form_data
	 * @param string $meta_key
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {}
}
