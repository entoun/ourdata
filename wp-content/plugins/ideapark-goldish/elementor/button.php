<?php

use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ideapark_Elementor_Button extends Widget_Base {
	/**
	 * Retrieve the widget name.
	 */
	public function get_name() {
		return 'ideapark-button';
	}

	/**
	 * Retrieve the widget title.
	 */
	public function get_title() {
		return esc_html__( 'Goldish Button', 'ideapark-goldish' );
	}

	/**
	 * Retrieve the widget icon.
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 */
	public function get_categories() {
		return [ 'ideapark-elements' ];
	}

	/**
	 * Register button widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_button',
			[
				'label' => __( 'Button', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'button_type',
			[
				'label'   => __( 'Type', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'ideapark-goldish' ),
					'outline' => __( 'Outline', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'button_size',
			[
				'label'   => __( 'Size', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'ideapark-goldish' ),
					'large'   => __( 'Large', 'ideapark-goldish' ),
				]
			]
		);


		$this->add_control(
			'text',
			[
				'label'       => __( 'Text', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Click here', 'ideapark-goldish' ),
				'placeholder' => __( 'Click here', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => __( 'Link', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
				'default'     => [
					'url' => '#',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'        => __( 'Alignment', 'ideapark-goldish' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'    => [
						'title' => __( 'Left', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => __( 'Center', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => __( 'Right', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'ideapark-goldish' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default'      => '',
			]
		);


		$this->add_control(
			'default_bg_color',
			[
				'label'     => __( 'Background Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button--default' => 'background-color: {{VALUE}};border-color: {{VALUE}};',
				],
				'condition' => [
					'button_type' => 'default'
				]
			]
		);

		$this->add_control(
			'default_text_color',
			[
				'label'     => __( 'Text Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button--default' => 'color: {{VALUE}};',
				],
				'condition' => [
					'button_type' => 'default'
				]
			]
		);

		$this->add_control(
			'outline_text_color',
			[
				'label'     => __( 'Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button--outline' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
				'condition' => [
					'button_type' => 'outline'
				]
			]
		);

		$this->add_control(
			'hover_bg_color',
			[
				'label'     => __( 'Background Color On Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'hover_text_color',
			[
				'label'     => __( 'Text Color On Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-button:hover' => 'color: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'       => __( 'Icon', 'ideapark-goldish' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => true,
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label'     => __( 'Icon Position', 'ideapark-goldish' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'left'  => __( 'Before', 'ideapark-goldish' ),
					'right' => __( 'After', 'ideapark-goldish' ),
				],
				'condition' => [
					'selected_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'view',
			[
				'label'   => __( 'View', 'ideapark-goldish' ),
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->add_control(
			'button_css_id',
			[
				'label'       => __( 'Button ID', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [
					'active' => true,
				],
				'default'     => '',
				'title'       => __( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'ideapark-goldish' ),
				'label_block' => false,
				'description' => __( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows <code>A-z 0-9</code> & underscore chars without spaces.', 'ideapark-goldish' ),
				'separator'   => 'before',

			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render button widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'c-ip-button__wrap' );

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'button', $settings['link'] );
		}

		$this->add_render_attribute( 'button', 'class', 'c-button c-ip-button' );
		$this->add_render_attribute( 'button', 'role', 'button' );

		if ( ! empty( $settings['button_css_id'] ) ) {
			$this->add_render_attribute( 'button', 'id', $settings['button_css_id'] );
		}

		if ( ! empty( $settings['button_type'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'c-button--' . $settings['button_type'] );
		}

		if ( ! empty( $settings['button_size'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'c-button--' . $settings['button_size'] );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<a <?php echo $this->get_render_attribute_string( 'button' ); ?>>
				<?php $this->render_text(); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Render button text.
	 *
	 * Render button widget text.
	 *
	 * @since  1.5.0
	 * @access protected
	 */
	protected function render_text() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( [
			'icon-align' => [
				'class' => [
					'c-ip-button__icon',
					'c-ip-button__icon--' . $settings['icon_align'],
				],
			],
			'text'       => [
				'class' => 'c-ip-button__text',
			],
		] );

		$this->add_inline_editing_attributes( 'text', 'none' );
		if ( ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon']['value'] ) ) {
			ob_start(); ?>
			<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>><?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>
			<?php
			$icon = ob_get_clean();
		} else {
			$icon = '';
		}
		?>
		<?php if ( $icon && $settings['icon_align'] == 'right' ) {
			echo ideapark_wrap( $icon );
		} ?>
		<span <?php echo $this->get_render_attribute_string( 'text' ); ?>><?php echo $settings['text']; ?></span>
		<?php if ( $icon && $settings['icon_align'] == 'left' ) {
			echo ideapark_wrap( $icon );
		} ?>
		<?php
	}

	/**
	 * Render icon list widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {
	}
}
