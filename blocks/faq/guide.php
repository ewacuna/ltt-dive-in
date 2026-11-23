<?php
/**
 * Block Guide content for Interactive Accordions.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$question_fields = array(
	array(
		'label'       => __( 'Question', 'ltt-dive-in' ),
		'required'    => true,
		'description' => __( 'This is the clickable line people see before they open the answer. Write it the way a visitor would actually ask it, and make sure it still makes sense if someone reads it on its own. Up to 160 characters.', 'ltt-dive-in' ),
	),
	array(
		'label'       => __( 'Answer', 'ltt-dive-in' ),
		'required'    => true,
		'description' => __( 'The text that shows up when the question is opened. You can use bold, lists, and links here. Try to answer the question in the first sentence and keep the rest short.', 'ltt-dive-in' ),
	),
	array(
		'label'       => __( 'Answer call to action', 'ltt-dive-in' ),
		'description' => __( 'An optional link shown under the answer, for when the full story lives on another page (for example, a permits page or a booking page). Give it a label that says where it goes.', 'ltt-dive-in' ),
	),
);

return array(
	'summary'        => __( 'A list of questions that open and close when clicked, so visitors can scan the questions and only read the answers they care about. It has three layouts: a single list, a list next to the heading, or questions split into topics with tabs.', 'ltt-dive-in' ),
	'where'          => __( 'Pages and blog posts. You can add it more than once on the same page.', 'ltt-dive-in' ),
	'steps'          => array(
		__( 'In the editor, click the + button and open the <strong>LTT Blocks</strong> group, or just type "accordion" or "faq" in the search.', 'ltt-dive-in' ),
		__( 'Add <strong>Interactive Accordions</strong>.', 'ltt-dive-in' ),
		__( 'In the panel on the right, pick an <strong>Accordion variant</strong> first. The fields below it change depending on the layout you choose.', 'ltt-dive-in' ),
		__( 'Fill in the heading and your questions. The preview in the editor updates once the required fields are filled in.', 'ltt-dive-in' ),
	),
	'fields_heading' => __( 'Fields every layout has', 'ltt-dive-in' ),
	'fields'         => array(
		array(
			'label'       => __( 'Accordion variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Picks the layout. See the layouts below to decide which one fits.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Heading', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The title of the section, like "Good to know before you go". Up to 90 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Introduction', 'ltt-dive-in' ),
			'description' => __( 'One or two sentences under the heading. Up to 240 characters. Leave it empty if the heading says enough.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Section call to action', 'ltt-dive-in' ),
			'description' => __( 'An optional button under the heading. Good for linking to a full FAQ page or a contact page. It only shows up if it has both a label and a link.', 'ltt-dive-in' ),
		),
	),
	'variants'       => array(
		'global'       => array(
			'name'        => __( 'Global Accordion', 'ltt-dive-in' ),
			'description' => __( 'The heading and introduction sit on top, and the questions run underneath in one full-width list. This is the default choice and the easiest one to read on a phone.', 'ltt-dive-in' ),
			'best_for'    => __( 'Most FAQ sections, especially when you have more than five or six questions.', 'ltt-dive-in' ),
			'fields'      => array_merge(
				array(
					array(
						'label'       => __( 'Frequently asked questions', 'ltt-dive-in' ),
						'required'    => true,
						'description' => __( 'The list of questions. Add at least one. Drag the rows to change the order. Each row has these fields:', 'ltt-dive-in' ),
					),
				),
				$question_fields
			),
		),
		'side_by_side' => array(
			'name'        => __( 'Side by Side Accordion', 'ltt-dive-in' ),
			'description' => __( 'Same fields as the Global Accordion, but on desktop the heading and introduction sit on the left and the questions sit on the right. On tablets and phones it stacks back into one column.', 'ltt-dive-in' ),
			'best_for'    => __( 'Shorter sets of three to six questions, where a full-width list would look a bit empty.', 'ltt-dive-in' ),
			'fields'      => array_merge(
				array(
					array(
						'label'       => __( 'Frequently asked questions', 'ltt-dive-in' ),
						'required'    => true,
						'description' => __( 'The list of questions. Add at least one. Each row has these fields:', 'ltt-dive-in' ),
					),
				),
				$question_fields
			),
		),
		'toggle'       => array(
			'name'        => __( 'Toggle Accordion', 'ltt-dive-in' ),
			'description' => __( 'Questions are grouped into topics. Visitors pick a topic from a row of tabs, and only that topic\'s questions are shown. The first topic is open when the page loads.', 'ltt-dive-in' ),
			'best_for'    => __( 'Long FAQs that cover different subjects, like "Summer", "Winter", and "Getting here".', 'ltt-dive-in' ),
			'fields'      => array_merge(
				array(
					array(
						'label'       => __( 'Category tag', 'ltt-dive-in' ),
						'description' => __( 'A short label above the heading, like "Plan your trip". It only shows on larger screens. Up to 40 characters.', 'ltt-dive-in' ),
					),
					array(
						'label'       => __( 'FAQ topics', 'ltt-dive-in' ),
						'required'    => true,
						'description' => __( 'Add between 2 and 4 topics. If there are fewer than two, the block will not show on the page.', 'ltt-dive-in' ),
					),
					array(
						'label'       => __( 'Topic label', 'ltt-dive-in' ),
						'required'    => true,
						'description' => __( 'The text on the tab. Keep it to one or two words so the tabs fit on a phone. Up to 40 characters.', 'ltt-dive-in' ),
					),
					array(
						'label'       => __( 'Topic questions', 'ltt-dive-in' ),
						'required'    => true,
						'description' => __( 'The questions for that topic. Add at least one. Each question has these fields:', 'ltt-dive-in' ),
					),
				),
				$question_fields
			),
			'notes'       => array(
				__( 'Try to keep the topics balanced. A tab with one question next to a tab with twelve feels odd.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'           => array(
		__( 'Only one question stays open at a time. When someone opens a new one, the previous one closes.', 'ltt-dive-in' ),
		__( 'Keyboard users can move between questions with the up and down arrow keys, and between topic tabs with the left and right arrow keys. You do not need to set anything up for this.', 'ltt-dive-in' ),
		__( 'Avoid link text like "click here" or "read more" inside answers. Write what the link is, for example "see the parking map".', 'ltt-dive-in' ),
		__( 'Put the most common questions first. Most people will not scroll through all of them.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor shows a box that says "Choose a variant and complete its required accordion fields".', 'ltt-dive-in' ) => __( 'The block is missing something it needs. Check that a variant is selected, the Heading has text, and there is at least one question (or at least two topics for the Toggle layout).', 'ltt-dive-in' ),
		__( 'The block shows in the editor but not on the live page.', 'ltt-dive-in' ) => __( 'Same cause as above. A block that is missing required content is hidden on the live site so visitors never see a half-empty section.', 'ltt-dive-in' ),
		__( 'I switched the variant and my questions are gone.', 'ltt-dive-in' ) => __( 'The Toggle layout keeps its questions inside topics, while the other two layouts share one list, so the fields you see change with the variant. Pick the layout before you write the questions.', 'ltt-dive-in' ),
	),
);
