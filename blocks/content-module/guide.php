<?php
/**
 * Block Guide content for Content Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$background_colors = __( 'White, Deep Navy, Peak Gold, Burgundy, Red, Forest Green, Green, Blue, or Crystal Clear Cyan. The text and button colors switch on their own so they stay readable on the color you pick.', 'ltt-dive-in' );

return array(
	'summary'        => __( 'The all-purpose block for text-led sections. One block, nine layouts: plain copy, copy next to a heading, bullet lists, icons with short blurbs, stats, a comparison table, testimonials, and two sign-up forms. If a section is mostly words, it probably belongs here.', 'ltt-dive-in' ),
	'where'          => __( 'Pages and blog posts. You can use it as many times as you like on one page, with a different layout each time.', 'ltt-dive-in' ),
	'steps'          => array(
		__( 'In the editor, click the + button and open <strong>LTT Blocks</strong>, or search for "content".', 'ltt-dive-in' ),
		__( 'Add <strong>Content Module</strong>.', 'ltt-dive-in' ),
		__( 'In the panel on the right, choose a <strong>Variant</strong>. Do this first: each layout has its own fields, and only the ones for the selected layout are shown.', 'ltt-dive-in' ),
		__( 'Fill in the fields. Required fields are marked, and WordPress will not let you save the post until they have content.', 'ltt-dive-in' ),
	),
	'fields_heading' => __( 'Choosing a layout', 'ltt-dive-in' ),
	'fields'         => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The layout for this section. It starts on Side by Side. Pick the layout before you start writing, because switching later shows a different set of fields and the text you typed for the old layout will not carry over.', 'ltt-dive-in' ),
		),
	),
	'variants_intro' => __( 'Here is what each layout looks like and what goes in each field.', 'ltt-dive-in' ),
	'variants'       => array(
		'side_by_side'      => array(
			'name'        => __( 'Side by Side', 'ltt-dive-in' ),
			'description' => __( 'A big heading on the left and the body text with buttons on the right. On phones the heading sits on top of the text. You can put a photo behind the whole section.', 'ltt-dive-in' ),
			'best_for'    => __( 'Introducing a page or a topic, like "Why visit in the fall".', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title on the left. Up to 120 characters, but shorter looks better at this size.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Body copy', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The text on the right. You can use bold, links, and lists. Do not repeat the heading here.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Background image', 'ltt-dive-in' ),
					'description' => __( 'An optional photo behind the section. The theme darkens it a little and switches the text to white so it stays readable. Pick a photo without a lot of busy detail.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Calls to action', 'ltt-dive-in' ),
					'description' => __( 'Up to three buttons under the text. With a background image they switch to a see-through "glass" style automatically.', 'ltt-dive-in' ),
				),
			),
		),
		'copy_block'        => array(
			'name'        => __( 'Copy Block', 'ltt-dive-in' ),
			'description' => __( 'A single column of formatted text, close to what you would get from a normal paragraph block but with the site\'s spacing and type styles.', 'ltt-dive-in' ),
			'best_for'    => __( 'Longer explanations, policies, or any section where you need headings, lists, and links mixed together.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Rich text content', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Everything goes in this one editor. Use the format menu for headings instead of making text bold and big. To change the bullet style of a list, use <strong>List style</strong> in the toolbar of the Visual tab.', 'ltt-dive-in' ),
				),
			),
		),
		'copy_block_list'   => array(
			'name'        => __( 'Copy Block List', 'ltt-dive-in' ),
			'description' => __( 'A heading followed by one or more lists that sit side by side. Each list can use icon bullets, round bullets, or numbers.', 'ltt-dive-in' ),
			'best_for'    => __( 'Packing lists, quick highlights, or step-by-step instructions.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Lists', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 1 to 6 lists.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'List style', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Icon bullets, Round bullets, or Numbered. Use Numbered only when the order matters, like steps.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Items', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 1 to 10 items per list. Each item can have bold text and links. Keep each one to a line or two.', 'ltt-dive-in' ),
				),
			),
		),
		'icon_block'        => array(
			'name'        => __( 'Icon Block', 'ltt-dive-in' ),
			'description' => __( 'A heading and intro at the top, then two to four columns, each with an icon, a short title, and a sentence or two.', 'ltt-dive-in' ),
			'best_for'    => __( 'Showing a few features or reasons at a glance, like "Free parking", "Dog friendly", "Open all year".', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Introduction', 'ltt-dive-in' ),
					'description' => __( 'A short paragraph under the heading. Up to 600 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Header call to action', 'ltt-dive-in' ),
					'description' => __( 'An optional button in the header area. Its style adjusts to the background color.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Background color', 'ltt-dive-in' ),
					'description' => $background_colors . ' ' . __( 'Leave it empty for white.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Icon blurbs', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 2 to 4 items.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Icon', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Must be an SVG file from the Media Library. PNG and JPG files will not be accepted. Ask your developer if you need a new icon exported.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Icon color', 'ltt-dive-in' ),
					'description' => __( 'Recolors the icon with one of the brand colors. Leave it empty to keep the colors the SVG already has.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Title', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'A few words under the icon. Up to 100 characters. An item without both a title and short copy is skipped on the page.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Short copy', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'One or two sentences. Up to 280 characters.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'Keep the items about the same length so the columns line up nicely.', 'ltt-dive-in' ),
			),
		),
		'newsletter_signup' => array(
			'name'        => __( 'Newsletter Sign Up', 'ltt-dive-in' ),
			'description' => __( 'A sign-up form on the left with its own heading and text, and an optional panel on the right that can point people somewhere else. The form itself comes from Gravity Forms.', 'ltt-dive-in' ),
			'best_for'    => __( 'Inviting people to subscribe, usually near the end of a page.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Form panel heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The title above the form, like "Get the Tahoe newsletter". Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Form panel description', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Tell people what they will get and how often. Up to 600 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Right panel heading', 'ltt-dive-in' ),
					'description' => __( 'Optional title for the right side panel.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Right panel description', 'ltt-dive-in' ),
					'description' => __( 'Optional text for the right side panel.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Right panel call to action', 'ltt-dive-in' ),
					'description' => __( 'Optional button in the right side panel.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Background image', 'ltt-dive-in' ),
					'description' => __( 'Optional photo behind the section.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Newsletter form', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Pick the form from the list. It shows every active form in Gravity Forms, so make sure you choose the newsletter one.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Form supporting text', 'ltt-dive-in' ),
					'description' => __( 'Small print under the form, usually a privacy note. Up to 200 characters. To add a link, write it like this: <code>[Privacy Policy](https://example.com/privacy)</code>.', 'ltt-dive-in' ),
				),
			),
		),
		'meetings_request'  => array(
			'name'        => __( 'Meetings Request', 'ltt-dive-in' ),
			'description' => __( 'A heading, a short description, and a Gravity Forms form for meeting and group requests.', 'ltt-dive-in' ),
			'best_for'    => __( 'The meetings and groups pages, where planners need to reach the team.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Description', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'What happens after someone sends the form, and how soon they will hear back. Up to 600 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Background image', 'ltt-dive-in' ),
					'description' => __( 'Optional photo behind the section.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Meetings request form', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Pick the meeting request form from the list of active Gravity Forms.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Form supporting text', 'ltt-dive-in' ),
					'description' => __( 'Small print under the form. Links work the same way as in the Newsletter layout: <code>[Link text](https://example.com)</code>.', 'ltt-dive-in' ),
				),
			),
		),
		'stats'             => array(
			'name'        => __( 'Stats', 'ltt-dive-in' ),
			'description' => __( 'Two to four big numbers, each with a short label, plus a heading and intro at the top.', 'ltt-dive-in' ),
			'best_for'    => __( 'Facts that are more impressive as numbers, like "72 miles of shoreline" or "300 days of sunshine".', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Category tag', 'ltt-dive-in' ),
					'description' => __( 'Optional short label above the heading. Up to 40 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Header copy', 'ltt-dive-in' ),
					'description' => __( 'Optional paragraph next to the heading. Up to 600 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Header call to action', 'ltt-dive-in' ),
					'description' => __( 'Optional button next to the heading.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Background color', 'ltt-dive-in' ),
					'description' => $background_colors,
				),
				array(
					'label'       => __( 'Background image', 'ltt-dive-in' ),
					'description' => __( 'Optional photo behind the section. If you add one, it replaces the background color.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Stats', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 2 to 4 stats.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Number or value', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Just the number, like "72" or "6,225". Up to 30 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Unit / context', 'ltt-dive-in' ),
					'description' => __( 'What the number is measured in, like "miles" or "ft". It is shown right with the number.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Label', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'A short line under the number that explains it, like "of shoreline around the lake".', 'ltt-dive-in' ),
				),
			),
		),
		'comparison_table'  => array(
			'name'        => __( 'Comparison Table', 'ltt-dive-in' ),
			'description' => __( 'A real table that compares two to four options side by side. Each row is a feature, and each cell can be text, a check mark, or an X.', 'ltt-dive-in' ),
			'best_for'    => __( 'Comparing passes, packages, or venues, where people want to see the differences at a glance.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Introduction', 'ltt-dive-in' ),
					'description' => __( 'Optional paragraph under the heading. Up to 600 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Feature column heading', 'ltt-dive-in' ),
					'description' => __( 'Optional title for the first column, the one that lists the features. Up to 80 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Comparison columns', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 2 to 4 columns, one for each option you are comparing. Each column needs a label (up to 60 characters) and an SVG icon, and can have a short description.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Comparison rows', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 1 to 5 rows. Each row has a feature label and one cell for each column. For each cell, choose <strong>Text value</strong> to type something short, or <strong>Included</strong> / <strong>Not included</strong> to show a check mark or an X.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Table call to action', 'ltt-dive-in' ),
					'description' => __( 'Optional button under the table.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'Fill in the cells in the same order as your columns. The first cell in each row goes with the first column, and so on. Cells for columns you did not add are ignored.', 'ltt-dive-in' ),
			),
		),
		'testimonial_block' => array(
			'name'        => __( 'Testimonial Block', 'ltt-dive-in' ),
			'description' => __( 'One quote, or several quotes that visitors can swipe through. With one testimonial it is a single quote. With two or more it turns into a carousel with arrows and dots.', 'ltt-dive-in' ),
			'best_for'    => __( 'Reviews from visitors, partners, or event planners.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Style', 'ltt-dive-in' ),
					'description' => __( 'Default shows the quote in the regular font. Light italic quote shows it in a lighter, slanted style that feels more personal.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Testimonials', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 1 to 10 quotes.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Quote', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The quote itself. Leave out the quotation marks, the theme adds them for you. Up to 400 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Photo', 'ltt-dive-in' ),
					'description' => __( 'Optional headshot of the person. Only use photos you have permission to publish.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Name', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Who said it. Up to 80 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Role or location', 'ltt-dive-in' ),
					'description' => __( 'Optional line under the name, like "Visitor from Sacramento". Up to 100 characters.', 'ltt-dive-in' ),
				),
			),
		),
	),
	'tips'           => array(
		__( 'Every layout except Copy Block uses its heading as the section title for screen readers, so give each one a clear, specific heading.', 'ltt-dive-in' ),
		__( 'Buttons only show up when they have both a label and a link. Use labels that say where they go, like "See trail maps", not "Learn more".', 'ltt-dive-in' ),
		__( 'When you add a background image, you do not need to write alt text for this block. The photo is treated as decoration.', 'ltt-dive-in' ),
		__( 'In the Testimonial Block, the editor shows all quotes stacked one under the other so you can check them. On the live page they turn into a carousel.', 'ltt-dive-in' ),
		__( 'Two Content Modules in a row with the same background can look like one section. Change the layout or the background color to break them up.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Select an active Gravity Form to preview this lead-capture module."', 'ltt-dive-in' ) => __( 'The form field is empty, or the form you picked was deactivated or deleted in Gravity Forms. Choose an active form and the preview will appear.', 'ltt-dive-in' ),
		__( 'WordPress will not let me save, and a field turns red.', 'ltt-dive-in' ) => __( 'Something is missing or over the limit. Check the message under the red field. The most common causes are too few or too many items (for example, one stat when the layout needs at least two) or an icon that is not an SVG.', 'ltt-dive-in' ),
		__( 'My background color is not showing.', 'ltt-dive-in' ) => __( 'If a background image is set, it wins over the color. Remove the image to see the color.', 'ltt-dive-in' ),
	),
);
