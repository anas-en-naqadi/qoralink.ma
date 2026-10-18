document.addEventListener( 'DOMContentLoaded', () => {
	const sliders = document.querySelectorAll( '.qora-hero-section' );

	sliders.forEach( ( slider ) => {
		const slides = slider.querySelectorAll( '.qora-hero-slide' );
		const buttons = slider.querySelectorAll( '.qora-hero-page-btn' );
		if ( slides.length <= 1 ) return;

		let current = 0;
		const autoplay = slider.getAttribute( 'data-autoplay' ) === 'true';
		const rawInterval = parseInt( slider.getAttribute( 'data-interval' ) || '7000', 10 );
		const intervalMs = ( rawInterval === 6000 || rawInterval === 3500 ) ? 7000 : rawInterval;
		let timer = null;

		const goTo = ( index ) => {
			current = ( index + slides.length ) % slides.length;
			slides.forEach( ( s, idx ) => {
				const active = idx === current;
				s.classList.toggle( 'is-active', active );
				s.setAttribute( 'aria-hidden', active ? 'false' : 'true' );
			} );
			buttons.forEach( ( b, idx ) => {
				const active = idx === current;
				b.classList.toggle( 'is-active', active );
				b.setAttribute( 'aria-selected', active ? 'true' : 'false' );
			} );
		};

		const startTimer = () => {
			if ( ! autoplay ) return;
			stopTimer();
			timer = setInterval( () => goTo( current + 1 ), intervalMs );
		};

		const stopTimer = () => {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		};

		buttons.forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				const idx = parseInt( btn.getAttribute( 'data-slide-to' ) || '0', 10 );
				goTo( idx );
				startTimer();
			} );
		} );

		slider.addEventListener( 'mouseenter', stopTimer );
		slider.addEventListener( 'mouseleave', startTimer );
		startTimer();
	} );
} );
