import * as pdfjs from './vendor/pdfjs/pdf.min.mjs';

pdfjs.GlobalWorkerOptions.workerSrc = new URL( './vendor/pdfjs/pdf.worker.min.mjs', import.meta.url ).href;

const standardFontDataUrl = new URL( './vendor/pdfjs/standard_fonts/', import.meta.url ).href;

document.querySelectorAll( '.parish-bulletin-pdf-viewer' ).forEach( function ( viewer ) {
	const url = viewer.dataset.pdfUrl;
	const loadingMessage = viewer.dataset.loadingMessage;
	const errorMessage = viewer.dataset.errorMessage;
	const canvas = viewer.querySelector( '[data-pdf-canvas]' );
	const canvasWrap = viewer.querySelector( '[data-pdf-canvas-wrap]' );
	const status = viewer.querySelector( '[data-pdf-status]' );
	const pageInput = viewer.querySelector( '[data-pdf-page]' );
	const pageCount = viewer.querySelector( '[data-pdf-page-count]' );
	const zoomLabel = viewer.querySelector( '[data-pdf-zoom]' );
	const previousButton = viewer.querySelector( '[data-pdf-action="previous"]' );
	const nextButton = viewer.querySelector( '[data-pdf-action="next"]' );
	const zoomOutButton = viewer.querySelector( '[data-pdf-action="zoom-out"]' );
	const zoomInButton = viewer.querySelector( '[data-pdf-action="zoom-in"]' );
	const context = canvas.getContext( '2d', { alpha: false } );

	let documentHandle = null;
	let currentPage = 1;
	let zoom = 1;
	let rendering = false;
	let pendingPage = null;
	let resizeTimer = null;

	function updateControls() {
		const ready = Boolean( documentHandle );
		pageInput.disabled = ! ready;
		previousButton.disabled = ! ready || currentPage <= 1;
		nextButton.disabled = ! ready || currentPage >= documentHandle.numPages;
		zoomOutButton.disabled = ! ready || zoom <= 0.6;
		zoomInButton.disabled = ! ready || zoom >= 2.4;
		pageInput.value = currentPage;
		zoomLabel.textContent = Math.round( zoom * 100 ) + '%';
	}

	async function renderPage( pageNumber ) {
		if ( rendering ) {
			pendingPage = pageNumber;
			return;
		}

		rendering = true;
		status.hidden = false;
		status.textContent = loadingMessage;

		try {
			const page = await documentHandle.getPage( pageNumber );
			const naturalViewport = page.getViewport( { scale: 1 } );
			const availableWidth = Math.max( 280, canvasWrap.clientWidth - 32 );
			const fitScale = availableWidth / naturalViewport.width;
			const viewport = page.getViewport( { scale: fitScale * zoom } );
			const outputScale = Math.min( window.devicePixelRatio || 1, 2 );

			canvas.width = Math.floor( viewport.width * outputScale );
			canvas.height = Math.floor( viewport.height * outputScale );
			canvas.style.width = Math.floor( viewport.width ) + 'px';
			canvas.style.height = Math.floor( viewport.height ) + 'px';
			canvas.hidden = false;

			await page.render( {
				canvasContext: context,
				viewport,
				transform: outputScale === 1 ? null : [ outputScale, 0, 0, outputScale, 0, 0 ],
			} ).promise;

			currentPage = pageNumber;
			status.hidden = true;
			updateControls();
		} catch ( error ) {
			status.hidden = false;
			status.textContent = errorMessage;
			canvas.hidden = true;
			console.error( 'Parish Bulletins PDF viewer:', error );
		} finally {
			rendering = false;
			if ( pendingPage !== null ) {
				const nextPage = pendingPage;
				pendingPage = null;
				renderPage( nextPage );
			}
		}
	}

	function requestPage( pageNumber ) {
		if ( ! documentHandle ) {
			return;
		}

		const requested = Math.max( 1, Math.min( documentHandle.numPages, Number( pageNumber ) || 1 ) );
		renderPage( requested );
	}

	previousButton.addEventListener( 'click', function () {
		requestPage( currentPage - 1 );
	} );

	nextButton.addEventListener( 'click', function () {
		requestPage( currentPage + 1 );
	} );

	pageInput.addEventListener( 'change', function () {
		requestPage( pageInput.value );
	} );

	zoomOutButton.addEventListener( 'click', function () {
		zoom = Math.max( 0.6, zoom - 0.2 );
		requestPage( currentPage );
	} );

	zoomInButton.addEventListener( 'click', function () {
		zoom = Math.min( 2.4, zoom + 0.2 );
		requestPage( currentPage );
	} );

	canvasWrap.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'ArrowLeft' || event.key === 'PageUp' ) {
			event.preventDefault();
			requestPage( currentPage - 1 );
		}
		if ( event.key === 'ArrowRight' || event.key === 'PageDown' ) {
			event.preventDefault();
			requestPage( currentPage + 1 );
		}
	} );

	window.addEventListener( 'resize', function () {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( function () {
			requestPage( currentPage );
		}, 180 );
	} );

	pdfjs.getDocument( {
		url,
		standardFontDataUrl,
	} ).promise.then( function ( loadedDocument ) {
		documentHandle = loadedDocument;
		pageCount.textContent = loadedDocument.numPages;
		pageInput.max = loadedDocument.numPages;
		updateControls();
		renderPage( 1 );
	} ).catch( function ( error ) {
		status.textContent = errorMessage;
		console.error( 'Parish Bulletins PDF viewer:', error );
	} );
} );
