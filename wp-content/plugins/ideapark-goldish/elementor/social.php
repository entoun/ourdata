<?php

use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ideapark_Elementor_Social extends Widget_Base {
	/**
	 * Retrieve the widget name.
	 */
	public function get_name() {
		return 'ideapark-social';
	}

	/**
	 * Retrieve the widget title.
	 */
	public function get_title() {
		return esc_html__( 'Social', 'ideapark-goldish' );
	}

	/**
	 * Retrieve the widget icon.
	 */
	public function get_icon() {
		return 'eicon-social-icons';
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
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_social_settings',
			[
				'label' => __( 'Settings', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'style',
			[
				'label'   => __( 'Style', 'ideapark-goldish' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'filled',
				'options' => [
					'filled'  => __( 'Filled', 'ideapark-goldish' ),
					'outline' => __( 'Outline', 'ideapark-goldish' ),
					'rounded' => __( 'Rounded', 'ideapark-goldish' ),
				]
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Icons Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-ip-social__link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color_hover',
			[
				'label'     => __( 'Icons Color on Hover', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-ip-social__link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => __( 'Icon size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 16,
				],
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 30,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-social' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'circle_size',
			[
				'label'      => __( 'Circle size', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 55,
				],
				'range'      => [
					'px' => [
						'min' => 15,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-social__link--rounded:before' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'style' => 'rounded'
				]
			]
		);

		$this->add_control(
			'circle_color',
			[
				'label'     => __( 'Circle Color', 'ideapark-goldish' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .c-ip-social__link--rounded:before' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'style' => 'rounded'
				]
			]
		);

		$this->add_responsive_control(
			'icon_space',
			[
				'label'      => __( 'Space', 'ideapark-goldish' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 30,
				],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					]
				],
				'devices'    => [ 'desktop', 'tablet', 'mobile' ],

				'selectors' => [
					'{{WRAPPER}} .c-ip-social__icon' => 'margin: calc({{SIZE}}{{UNIT}} / 2);',
					'{{WRAPPER}} .c-ip-social'       => 'margin: calc(-{{SIZE}}{{UNIT}} / 2);'
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_social_links',
			[
				'label' => __( 'Social links', 'ideapark-goldish' ),
			]
		);


		$this->add_control(
			'soc-facebook',
			[
				'label'       => __( 'Facebook url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-instagram',
			[
				'label'       => __( 'Instagram url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-vk',
			[
				'label'       => __( 'VK url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-ok',
			[
				'label'       => __( 'OK url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-telegram',
			[
				'label'       => __( 'Telegram url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-whatsapp',
			[
				'label'       => __( 'Whatsapp url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-twitter',
			[
				'label'       => __( 'Twitter url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-youtube',
			[
				'label'       => __( 'YouTube url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-vimeo',
			[
				'label'       => __( 'Vimeo url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-snapchat',
			[
				'label'       => __( 'Snapchat url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-tiktok',
			[
				'label'       => __( 'TikTok url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-linkedin',
			[
				'label'       => __( 'LinkedIn url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-flickr',
			[
				'label'       => __( 'Flickr url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-tumblr',
			[
				'label'       => __( 'Tumblr url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);

		$this->add_control(
			'soc-github',
			[
				'label'       => __( 'Github url', 'ideapark-goldish' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'placeholder' => __( 'https://your-link.com', 'ideapark-goldish' ),
			]
		);
		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$soc_count = 0;
		ob_start();
		foreach ( $settings as $item_index => $row ) {
			if ( strpos( $item_index, 'soc-' ) !== false && ! empty( $row['url'] ) ) {
				$soc_count ++;

				$link_key = 'link_' . $item_index;

				$this->add_link_attributes( $link_key, $row );
				$this->add_render_attribute( $link_key, 'class', 'c-ip-social__link c-ip-social__link--' . $settings['style'] );

				$soc_index = str_replace( 'soc-', '', $item_index );
				?>
				<a <?php echo $this->get_render_attribute_string( $link_key ); ?>><i
						class="ip-<?php echo esc_attr( ( $settings['style'] == 'outline' ? 'o-' : '' ) . $soc_index ) ?> c-ip-social__icon c-ip-social__icon--<?php echo esc_attr( $soc_index ) ?>">
						<!-- --></i></a>
			<?php };
		}
		$content = ob_get_clean();
		echo ideapark_wrap( $content, '<div class="c-ip-social">', '</div>' );
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 */
	protected function content_template() {

	}
}
