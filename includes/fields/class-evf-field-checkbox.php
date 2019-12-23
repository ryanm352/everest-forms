<?php
/**
 * Checkbox field.
 *
 * @package EverestForms\Fields
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * EVF_Field_Checkbox class.
 */
class EVF_Field_Checkbox extends EVF_Form_Fields {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name     = esc_html__( 'Checkboxes', 'everest-forms' );
		$this->type     = 'checkbox';
		$this->icon     = 'evf-icon evf-icon-checkbox';
		$this->order    = 70;
		$this->group    = 'general';
		$this->defaults = array(
			1 => array(
				'label'   => esc_html__( 'First Choice', 'everest-forms' ),
				'value'   => '',
				'default' => '',
			),
			2 => array(
				'label'   => esc_html__( 'Second Choice', 'everest-forms' ),
				'value'   => '',
				'default' => '',
			),
			3 => array(
				'label'   => esc_html__( 'Third Choice', 'everest-forms' ),
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
					'choices_images',
					'description',
					'required',
				),
			),
			'advanced-options' => array(
				'field_options' => array(
					'randomize',
					'show_values',
					'input_columns',
					'choice_limit',
					'label_hide',
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
		add_filter( 'everest_forms_html_field_value', array( $this, 'html_field_value' ), 10, 4 );
		add_filter( 'everest_forms_field_properties_' . $this->type, array( $this, 'field_properties' ), 5, 3 );
	}

	/**
	 * Return images, if any, for HTML supported values.
	 *
	 * @since 1.6.0
	 *
	 * @param string $value     Field value.
	 * @param array  $field_val Field settings.
	 * @param array  $form_data Form data and settings.
	 * @param string $context   Value display context.
	 *
	 * @return string
	 */
	public function html_field_value( $value, $field_val, $form_data = array(), $context = '' ) {
		if ( is_serialized( $field_val ) ) {
			$value = maybe_unserialize( $field_val );

			if (
				isset( $value['label'], $value['image'] )
				&& $this->type === $value['type']
				&& 'entry-table' !== $context
				&& apply_filters( 'everest_forms_checkbox_field_html_value_images', true, $context )
			) {
				$items = array();

				foreach ( $value as $key => $val ) {
					if ( ! empty( $value['images'][ $key ] ) ) {
						$items[] = sprintf(
							'<span style="max-width:200px;display:block;margin:0 0 5px 0;"><img src="%s" style="max-width:100%%;display:block;margin:0;"></span>%s',
							esc_url( $value['images'][ $key ] ),
							esc_html( $val )
						);
					} else {
						$items[] = esc_html( $val );
					}
				}

				return implode( '<br><br>', $items );
			}
		}

		return $value;
	}

	/**
	 * Define additional field properties.
	 *
	 * @param array $properties Field properties.
	 * @param array $field Field data.
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {
		// Define data.
		$form_id  = absint( $form_data['id'] );
		$field_id = $field['id'];
		$choices  = $field['choices'];

		// Remove primary input.
		unset( $properties['inputs']['primary'] );

		// Set input container (ul) properties.
		$properties['input_container'] = array(
			'class' => array( ! empty( $field['random'] ) ? 'everest-forms-randomize' : '' ),
			'data'  => array(),
			'attr'  => array(),
			'id'    => "evf-{$form_id}-field_{$field_id}",
		);

		// Set choice limit.
		$field['choice_limit'] = empty( $field['choice_limit'] ) ? 0 : (int) $field['choice_limit'];
		if ( $field['choice_limit'] > 0 ) {
			$properties['input_container']['data']['choice-limit'] = $field['choice_limit'];
		}

		// Set input properties.
		foreach ( $choices as $key => $choice ) {
			$depth = isset( $choice['depth'] ) ? absint( $choice['depth'] ) : 1;

			// Choice labels should not be left blank, but if they are we provide a basic value.
			$value = isset( $field['show_values'] ) ? $choice['value'] : $choice['label'];
			if ( '' === $value ) {
				if ( 1 === count( $choices ) ) {
					$value = esc_html__( 'Checked', 'everest-forms' );
				} else {
					/* translators: %s - Choice Number. */
					$value = sprintf( esc_html__( 'Choice %s', 'everest-forms' ), $key );
				}
			}

			$properties['inputs'][ $key ] = array(
				'container' => array(
					'attr'  => array(),
					'class' => array( "choice-{$key}", "depth-{$depth}" ),
					'data'  => array(),
					'id'    => '',
				),
				'label'     => array(
					'attr'  => array(
						'for' => "evf-{$form_id}-field_{$field_id}_{$key}",
					),
					'class' => array( 'everest-forms-field-label-inline' ),
					'data'  => array(),
					'id'    => '',
					'text'  => $choice['label'],
				),
				'attr'      => array(
					'name'  => "everest_forms[form_fields][{$field_id}][]",
					'value' => $value,
				),
				'class'     => array(),
				'data'      => array(),
				'id'        => "evf-{$form_id}-field_{$field_id}_{$key}",
				'image'     => isset( $choice['image'] ) ? $choice['image'] : '',
				'required'  => ! empty( $field['required'] ) ? 'required' : '',
				'default'   => isset( $choice['default'] ),
			);

			// Rule for validator only if needed.
			if ( $field['choice_limit'] > 0 ) {
				$properties['inputs'][ $key ]['data']['rule-check-limit'] = 'true';
			}
		}

		// Required class for validation.
		if ( ! empty( $field['required'] ) ) {
			$properties['input_container']['class'][] = 'evf-field-required';
		}

		// Custom properties if enabled image choices.
		if ( ! empty( $field['choices_images'] ) ) {
			$properties['input_container']['class'][] = 'everest-forms-image-choices';

			foreach ( $properties['inputs'] as $key => $inputs ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'everest-forms-image-choices-item';
			}
		}

		// Add selected class for choices with defaults.
		foreach ( $properties['inputs'] as $key => $inputs ) {
			if ( ! empty( $inputs['default'] ) ) {
				$properties['inputs'][ $key ]['container']['class'][] = 'everest-forms-selected';
			}
		}

		return $properties;
	}

	/**
	 * Randomize order of choices.
	 *
	 * @since 1.6.0
	 * @param array $field Field Data.
	 */
	public function randomize( $field ) {
		$args = array(
			'slug'    => 'random',
			'content' => $this->field_element(
				'checkbox',
				$field,
				array(
					'slug'    => 'random',
					'value'   => isset( $field['random'] ) ? '1' : '0',
					'desc'    => esc_html__( 'Randomize Choices', 'everest-forms' ),
					'tooltip' => esc_html__( 'Check this option to randomize the order of the choices.', 'everest-forms' ),
				),
				false
			),
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Choice limit field option.
	 *
	 * @since 1.6.0
	 * @param array $field Field data.
	 */
	public function choice_limit( $field ) {
		$choice_limit_label = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'choice_limit',
				'value'   => esc_html__( 'Choice Limit', 'everest-forms' ),
				'tooltip' => esc_html__( 'Check this option to limit the number of checkboxes a user can select.', 'everest-forms' ),
			),
			false
		);
		$choice_limit_input = $this->field_element(
			'text',
			$field,
			array(
				'slug'  => 'choice_limit',
				'value' => ( isset( $field['choice_limit'] ) && $field['choice_limit'] > 0 ) ? (int) $field['choice_limit'] : '',
				'type'  => 'number',
			),
			false
		);

		$args = array(
			'slug'    => 'choice_limit',
			'content' => $choice_limit_label . $choice_limit_input,
		);
		$this->field_element( 'row', $field, $args );
	}

	/**
	 * Show values field option.
	 *
	 * @param array $field Field Data.
	 */
	public function show_values( $field ) {
		// Show Values toggle option. This option will only show if already used or if manually enabled by a filter.
		if ( ! empty( $field['show_values'] ) || apply_filters( 'everest_forms_fields_show_options_setting', false ) ) {
			$args = array(
				'slug'    => 'show_values',
				'content' => $this->field_element(
					'checkbox',
					$field,
					array(
						'slug'    => 'show_values',
						'value'   => isset( $field['show_values'] ) ? $field['show_values'] : '0',
						'desc'    => __( 'Show Values', 'everest-forms' ),
						'tooltip' => __( 'Check this to manually set form field values.', 'everest-forms' ),
					),
					false
				),
			);
			$this->field_element( 'row', $field, $args );
		}
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {
		// Label.
		$this->field_preview_option( 'label', $field );

		// Choices.
		$this->field_preview_option( 'choices', $field );

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $field_atts Field attributes.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $field_atts, $form_data ) {

		// Setup and sanitize the necessary data
		$primary     = $field['properties']['inputs']['primary'];
		$field       = apply_filters( 'everest_forms_checkbox_field_display', $field, $field_atts, $form_data );
		$field_class = implode( ' ', array_map( 'sanitize_html_class', $field_atts['input_class'] ) );
		$field_id    = implode( ' ', array_map( 'sanitize_html_class', $field_atts['input_id'] ) );
		$field_data  = '';
		$form_id     = $form_data['id'];
		$choices     = isset( $field['choices'] ) ? $field['choices'] : array();
		if ( ! empty( $field_atts['input_data'] ) ) {
			foreach ( $field_atts['input_data'] as $key => $val ) {
				$field_data .= ' data-' . $key . '="' . $val . '"';
			}
		}
		// List.
		printf( '<ul id="%s" class="%s" %s>', $field_id, $field_class, $field_data );

		foreach ( $choices as $key => $choice ) {

			$selected = isset( $choice['default'] ) ? '1' : '0';
			$val      = isset( $field['show_values'] ) ? esc_attr( $choice['value'] ) : esc_attr( $choice['label'] );
			$depth    = isset( $choice['depth'] ) ? absint( $choice['depth'] ) : 1;
			$id       = $primary['id'] . '_' . $key;

			printf( '<li class="choice-%d depth-%d">', $key, $depth );

			// Checkbox elements.
			printf(
				'<input type="checkbox" value="%s" %s %s>',
				esc_attr( $val ),
				evf_html_attributes( $id, $primary['class'], $primary['data'], $primary['attr'] ),
				esc_attr( $primary['required'] )
			);
			printf( '<label class="everest-forms-field-label-inline" for="evf-%d-field_%s_%d">%s</label>', $form_id, $field['id'], $key, wp_kses_post( $choice['label'] ) );

			echo '</li>';
		}

		echo '</ul>';
	}

	/**
	 * Formats and sanitizes field.
	 *
	 * @param int    $field_id
	 * @param array  $field_submit
	 * @param array  $form_data
	 * @param string $meta_key
	 */
	public function format( $field_id, $field_submit, $form_data, $meta_key ) {

		$field_submit = (array) $field_submit;
		$field        = $form_data['form_fields'][ $field_id ];
		$dynamic      = ! empty( $field['dynamic_choices'] ) ? $field['dynamic_choices'] : false;
		$name         = sanitize_text_field( $field['label'] );
		$value_raw    = evf_sanitize_array_combine( $field_submit );

		$data = array(
			'name'      => $name,
			'value'     => '',
			'value_raw' => $value_raw,
			'id'        => $field_id,
			'type'      => $this->type,
			'meta_key'  => $meta_key,
		);

		if ( 'post_type' === $dynamic && ! empty( $field['dynamic_post_type'] ) ) {
			// Dynamic population is enabled using post type.
			$value_raw                 = implode( ',', array_map( 'absint', $field_submit ) );
			$data['value_raw']         = $value_raw;
			$data['dynamic']           = 'post_type';
			$data['dynamic_items']     = $value_raw;
			$data['dynamic_post_type'] = $field['dynamic_post_type'];
			$posts                     = array();

			foreach ( $field_submit as $id ) {
				$post = get_post( $id );

				if ( ! is_wp_error( $post ) && ! empty( $post ) && $data['dynamic_post_type'] === $post->post_type ) {
					$posts[] = esc_html( $post->post_title );
				}
			}

			$data['value'] = ! empty( $posts ) ? evf_sanitize_array_combine( $posts ) : '';

		} elseif ( 'taxonomy' === $dynamic && ! empty( $field['dynamic_taxonomy'] ) ) {

			// Dynamic population is enabled using taxonomy
			$value_raw                = implode( ',', array_map( 'absint', $field_submit ) );
			$data['value_raw']        = $value_raw;
			$data['dynamic']          = 'taxonomy';
			$data['dynamic_items']    = $value_raw;
			$data['dynamic_taxonomy'] = $field['dynamic_taxonomy'];
			$terms                    = array();

			foreach ( $field_submit as $id ) {
				$term = get_term( $id, $field['dynamic_taxonomy'] );

				if ( ! is_wp_error( $term ) && ! empty( $term ) ) {
					$terms[] = esc_html( $term->name );
				}
			}

			$data['value'] = ! empty( $terms ) ? evf_sanitize_array_combine( $terms ) : '';

		} else {

			// Normal processing, dynamic population is off
			// If show_values is true, that means values posted are the raw values
			// and not the labels. So we need to get the label values.
			if ( ! empty( $field['show_values'] ) && '1' == $field['show_values'] ) {

				$value = array();

				foreach ( $field_submit as $field_submit_single ) {
					foreach ( $field['choices'] as $choice ) {
						if ( $choice['value'] == $field_submit_single ) {
							$value[] = $choice['label'];
							break;
						}
					}
				}

				$data['value'] = ! empty( $value ) ? evf_sanitize_array_combine( $value ) : '';

			} else {
				$data['value'] = $value_raw;
			}
		}

		// Push field details to be saved.
		EVF()->task->form_fields[ $field_id ] = $data;
	}
}
