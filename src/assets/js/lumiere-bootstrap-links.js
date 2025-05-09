/**
 * BOOTSTRAP popins
 * Functions are Content Security Policy (CSP) compliant
 *
 * (A) Build bootstrap modal window according to the classes clicked
 * This function is triggered on click on data attribute "modal_window(.*)"
 * 1. Extracts info from data-(.*) <a> attribute to build a link inserted into an object, then create the modal
 * 2. Add a spinner with the class .showspinner, which is removed once the object is loaded
 * 3. Using nonce (@since 4.2.3)
 * 4.Select the popin width in bootstrapConvertModalSize() (@since 4.6.1)
 * 5. Creates an individual popin (@since 4.6.1)
 */
document.addEventListener('DOMContentLoaded', () => {

	// Convert the selected option lumiere_vars.popupLarg to its corresponding
	function bootstrapConvertModalSize(imdbAdminValues) {
		const MODAL_STANDARD_WIDTH = {
			300: 'modal-sm',
			500: '',
			800: 'modal-lg',
			1140: 'modal-xl'
		};

		let modalSizeName = '';
		let imdbpopuplarg = lumiere_vars.popupLarg;
		for (const sizeWidth of Object.keys(MODAL_STANDARD_WIDTH).map(Number)) {
			if (imdbpopuplarg >= sizeWidth) {
				modalSizeName = ' ' + MODAL_STANDARD_WIDTH[sizeWidth];
			}
		}
		return modalSizeName;
	}

	// addSpinnerAndLoadModal()
	const addSpinnerAndLoadModal = (event, dataAttr, urlBase, urlStringCat) => {
	
		event.preventDefault();

		// Get the clicked link's context
		const $clickedLink = jQuery(event.currentTarget);
		const modalId = `theModal${$clickedLink.data(dataAttr)}`; // Unique modal ID for the clicked link

		// Dynamically create the modal structure for the clicked link
		if (jQuery(`#${modalId}`).length === 0) {
			const modalHtml = `
				<span class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
					<span id="bootstrap${modalId}" class="modal-dialog modal-dialog-centered ${bootstrapConvertModalSize()}" role="document">
						<span class="modal-content">
							<span class="modal-header bootstrap_black">
								<span id="lumiere_bootstrap_spinner_id" role="status" class="spinner-border">
									<span class="sr-only"></span>
								</span>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-target="theModal${modalId}"></button>
							</span>
							<span class="modal-body embed-responsive embed-responsive-16by9"></span>
						</span>
					</span>
				</span>`;
			jQuery('body').append(modalHtml);
		}

		const $modal = jQuery(`#${modalId}`);
		const $modalHeader = $modal.find('.modal-header');
		const $modalBody = $modal.find('.modal-body');

		// Add the class showing the spinner after click
		$modalHeader.addClass('showspinner');

		// Get the data-modal_window_* value
		const modalValue = $clickedLink.data(dataAttr);
		const modalNonce = $clickedLink.data('modal_window_nonce');
		const url = `${urlBase}?${urlStringCat}=${modalValue}&_wpnonce=${modalNonce}`;

		// Build the object then activate bootstrap popup link
		$modalBody.html(`<object id="${modalValue}" name="${modalValue}" data="${url}"/>`);
		$modal.modal('show');

		// Remove the spinner once the object is loaded
		// The bootstrap modal is normally saved and doesn't detect a second click on the same object; so we disable the cache
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
