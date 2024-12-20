/**
 * BOOTSRAP popins
 * Function are Content Security Policy (CSP) compliant
 *
 * (A) Build bootstrap modal window according to the classes clicked
 * This function is triggered on click on data attribute "modal_window(.*)"
 * 1- Extracts info from data-(.*) <a> attribute to build a link inserted into an object, then create the modal
 * 2- Add a spinner with the class .showspinner, which is removed once the object is loaded
 * 3- Using nonce (@since 4.2.3)
 */
document.addEventListener('DOMContentLoaded', () => {

	// addSpinnerAndLoadModal()
	const addSpinnerAndLoadModal = (event, dataAttr, urlBase, urlStringCat) => {
		event.preventDefault();
		const $modalHeader = jQuery('.modal-header');
		const $modalBody = jQuery('.modal-body');

		// Add the class showing the spinner after click
		$modalHeader.addClass('showspinner');

		// get the data-modal_window_* value
		const modalValue = jQuery(event.currentTarget).data(dataAttr);

		const modalNonce = jQuery(event.currentTarget).data('modal_window_nonce');

		const url = `${urlBase}?${urlStringCat}=${modalValue}&_wpnonce=${modalNonce}`;

		// Build the object then activate bootstrap popup link
		$modalBody.html(`<object id="${modalValue}" name="${modalValue}" data="${url}"/>`);
		jQuery(`#theModal${modalValue}`).modal('show');

		// Remove the spinner once the object is loaded
		// The bootstrap modal is normaly saved and doesn't detect a second click on the same object; so we disable the cache
		jQuery.ajax({
			url,
			headers: {
				'Cache-Control': 'no-cache, no-store, must-revalidate',
				'Pragma': 'no-cache',
				'Expires': '0'
			},
			success: () => {
				$modalHeader.removeClass('showspinner');
			}
		});
	};
		
	/** bootstrap popup **People's name**, data-modal_window_people */

	jQuery('a[data-modal_window_people]').on('click', (event) => {
		addSpinnerAndLoadModal(event, 'modal_window_people', lumiere_vars.urlpopup_person, 'mid');
	});

	/** bootstrap popup **Movie by IMDb Title**, data-modal_window_film */

	jQuery('a[data-modal_window_film]').on('click', (event) => {
		addSpinnerAndLoadModal(event, 'modal_window_film', lumiere_vars.urlpopup_film, 'film');
	});

	/** bootstrap popup **Movie by IMDb ID**, data-modal_window_filmid */

	jQuery('a[data-modal_window_filmid]').on('click', (event) => {
		addSpinnerAndLoadModal(event, 'modal_window_filmid', lumiere_vars.urlpopup_film, 'mid');
	});
});
