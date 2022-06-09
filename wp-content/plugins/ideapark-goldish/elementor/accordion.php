<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Accordion widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ideapark_Elementor_Accordion extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve Accordion widget name.
	 *
	 * @return string Widget name.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ideapark-accordion';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Accordion widget title.
	 *
	 * @return string Widget title.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Goldish Accordion', 'ideapark-goldish' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Accordion widget icon.
	 *
	 * @return string Widget icon.
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'eicon-accordion';
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
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 * @since  2.1.0
	 * @access public
	 *
	 */
	public function get_keywords() {
		return [ 'accordion', 'icon', 'list', 'FAQ' ];
	}

	/**
	 * Register Accordion widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_list',
			[
				'label' => __( 'Accordion items', 'ideapark-goldish' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			[
				'label'       => __( 'Title', 'ideapark-goldish' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => __( 'Enter title', 'ideapark-goldish' ),
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'content',
			[
				'label'       => __( 'Content', 'ideapark-goldish' ),
				'type'        => \Elementor\Controls_Manager::WYSIWYG,
				'label_block' => true
			]
		);

		$this->add_control(
			'accordion',
			[
				'label'       => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'title'   => __( 'Title #1', 'ideapark-goldish' ),
						'content' => __( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'ideapark-goldish' ),
					],
					[
						'title'   => __( 'Title #2', 'ideapark-goldish' ),
						'content' => __( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'ideapark-goldish' ),
					],
					[
						'title'   => __( 'Title #3', 'ideapark-goldish' ),
						'content' => __( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'ideapark-goldish' ),
					],
				],
				'title_field' => '{{{ title }}}',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render Accordion widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="c-ip-accordion">
			<div class="c-ip-accordion__wrap">
				<div class="c-ip-accordion__list">
					<?php
					foreach ( $settings['accordion'] as $index => $item ) { ?>
					<div class="c-ip-accordion__item">
						<?php echo ideapark_wrap( esc_html( $item['title'] ), '<a class="c-ip-accordion__link js-accordion-title" href="" onclick="return false;"><div class="c-ip-accordion__header">', '</div><i class="ip-plus_big c-ip-accordion__arrow"></i></a>' ) ?>
						<?php echo ideapark_wrap( do_shortcode( $item['content'] ), '<div class="c-ip-accordion__content entry-content">', '</div>' ); ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Accordion widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function content_template() {
	}
}
