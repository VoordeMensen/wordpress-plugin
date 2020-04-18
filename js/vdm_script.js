if (window.addEventListener) {
	window.addEventListener("message", message_listener);
} else {
	window.attachEvent("onmessage", message_listener);
}
function message_listener(event) {
	if (event.data.vdm_basketcounter) {
		jQuery('.vdm_basketcounter').html(event.data.vdm_basketcounter);
	}
}