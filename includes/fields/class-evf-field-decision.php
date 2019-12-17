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
		$this->defaults = array(
			1 => array(
				'label'   => esc_html__( 'I consent to having this website store my submitted information so they can respond to my inquiry.', 'everest-forms' ),
				'value'   => '',
				'default' => '',
			),
		);
		$this->settings = array(
			'basic-options'    => array(
				'field_options' => array(
					'label',
					'meta',
					'choices',
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
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_filter( 'everest_forms_field_new_required', array( $this, 'field_default_required' ), 5, 3 );
	}

	/**
	 * Field should default to being required.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $required Required status, true is required.
	 * @param array $field    Field settings.
	 *
	 * @return bool
	 */
	public function field_default_required( $required, $field ) {
		if ( $this->type === $field['type'] ) {
			return true;
		}

		return $required;
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Choice.
		$this->field_preview_option( 'choices', $field );

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
