function browser_type() {

	var userAgent = window.navigator.userAgent.toLowerCase();
	var appVersion = window.navigator.appVersion.toLowerCase();

	if (userAgent.indexOf('opera') != -1) {
		return 'opera';
	} else if (userAgent.indexOf('msie') != -1) {
		if (appVersion.indexOf("msie 6.") != -1) {
			return 'ie6';
		} else if (appVersion.indexOf("msie 7.") != -1) {
			return 'ie7';
		} else if (appVersion.indexOf("msie 8.") != -1) {
			return 'ie8';
		} else {
			return 'ie';
		}
	} else if (userAgent.indexOf('chrome') != -1) {
		return 'chrome';
	} else if (userAgent.indexOf('safari') != -1) {
		return 'safari';
	} else if (userAgent.indexOf('gecko') != -1) {
		return 'gecko';
	} else {
		return false;
	}
}
