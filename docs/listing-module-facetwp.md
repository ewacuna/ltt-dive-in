# Listing Module: FacetWP setup

This theme supplies the Listing Module integration. FacetWP remains a separate WordPress plugin; its facet definitions live in the WordPress database and should be exported when moving the site to another environment.

## Create the facets

Keep the two existing taxonomy Dropdown facets for Location and Stay Type. Check that each facet's **Data source** is the taxonomy attached to the content type selected in the block.

Create a keyword-search facet in **Settings → FacetWP → Facets**:

1. Add a facet named `listing_search` (or another unique name) and select **Search** as its type.
2. Set **Search engine** to **WP Default**.
3. Turn on **Auto refresh** and **Order by relevance**.
4. Save the facet.

Create a Load More facet:

1. Add a facet named `listing_load_more` (or another unique name) and select **Pager** as its type.
2. Set its pager type to **Load more**.
3. Set the button label to **Load More** and the loading label to **Loading…**.
4. Save the facet.

The Listing Module's editor selectors list only Search facets using WP Default with Auto refresh and Order by relevance enabled, plus Pager facets set to Load More. FacetWP dropdown selectors list taxonomy Dropdown facets. The block checks that its dropdown taxonomies belong to its selected content type and skips duplicate filters.

## Connect the facets in the block

In the Listing Module block settings, choose:

- **Content type**: the public post type containing the cards.
- **First dropdown filter** and **Second dropdown filter**: two different taxonomies for that content type.
- **Search facet**: `listing_search`.
- **Load More facet**: `listing_load_more`.
- **Initial card count**: how many cards appear first (1–12).
- **Load More step**: how many additional cards each click adds (1–12). New blocks default to three; older blocks without a saved value use their initial count.

Save the page and test the front end. A keyword search combines with both dropdowns, and the conditions narrow the same card set together. Changing a filter or search term replaces the visible cards and resets the pager. Load More appends the next batch without reloading the page.

## Re-index and move to another environment

FacetWP indexes content and taxonomy values for filtering. Re-index after adding a taxonomy, changing which content types or taxonomies FacetWP indexes, or otherwise changing the indexed data. Creating a Search or Pager facet alone does not add taxonomy values to the index.

Export the facet definitions under **Settings → FacetWP → Settings → Import/Export** after creating them, then import that JSON in each destination environment. The theme's ACF Local JSON versions the block fields; it does not contain the FacetWP facet configuration or card content. Re-index on the destination if its content or index settings differ.

Keep one FacetWP-powered Listing Module on each page. FacetWP runs one listing query per page, and this integration scopes the connected facets to that listing.

FacetWP references: [Search facet settings and behavior](https://facetwp.com/help-center/facets/facet-types/search/) and [different first-page and later-page sizes](https://facetwp.com/how-to-use-a-different-post-per-page-on-the-first-page/).

## Verification checklist

- Test each dropdown separately, both dropdowns together, and both with a keyword. Confirm the selections narrow the same cards together.
- Search for text found in a title, an excerpt, and the main post content. Try a term with no matches, then clear the search and filters.
- Type quickly and check that the latest term wins. Press Enter and use the search icon. Confirm the search facet returns more than 200 matching posts when that many are available.
- Check initial/step combinations of 3/3, 3/6, and 6/3, including a final partial batch. Try double-clicking Load More and changing a filter after several batches.
- Check keyboard-only operation, visible focus, result announcements, mobile carousel arrows, 320px reflow, 200% zoom, and reduced motion.

## What happens when settings are missing

The editor preview reports missing or incompatible facet choices. On the front end, only configured controls are shown. If FacetWP is inactive, the block still displays its initial WordPress results, with no non-working filter or pager controls. Without JavaScript, the initial cards remain visible and a note explains that the interactive controls need JavaScript.
