/*--------------------------------------*/

/*Scroll top button
/*--------------------------------------*/
const scrollTop = document.querySelector('.scroll-top');
if (scrollTop) {
	const togglescrollTop = function() {
	  window.scrollY > 200 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
	}
	window.addEventListener('load', togglescrollTop);
	document.addEventListener('scroll', togglescrollTop);
	scrollTop.addEventListener('click', window.scrollTo({
	  top: 0,
	  behavior: 'smooth'
	}));
}
/*--------------------------------------*/

/*Effect
/*--------------------------------------*/
jQuery(document).ready(function($){
	$(function(){
		AOS.init({
			disable: 'phone',
			duration: 1200,
		});
	});
});
/*--------------------------------------*/