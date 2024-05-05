if (window.addEventListener) {
	window.addEventListener("message", message_listener);
} else {
	window.attachEvent("onmessage", message_listener);
}
function message_listener(event) {
	if (event.data.voordemensen_basketcounter) {
		jQuery('.voordemensen_basketcounter').html(event.data.voordemensen_basketcounter);
	}
}