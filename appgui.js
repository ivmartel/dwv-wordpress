/**
 * Application GUI.
 */

// decode query
dwv.utils.decodeQuery = dwv.utils.base.decodeQuery;
// Progress
dwv.gui.displayProgress = function (/*percent*/) { /*does nothing*/ };
// get element
dwv.gui.getElement = dwv.gui.base.getElement;
// refresh
dwv.gui.refreshElement = dwv.gui.base.refreshElement;

// Default window level presets.
dwv.tool.defaultpresets = {};
// Default window level presets for CT.
dwv.tool.defaultpresets.CT = {
    "mediastinum": {"center": 40, "width": 400},
    "lung": {"center": -500, "width": 1500},
    "bone": {"center": 500, "width": 2000},
    "brain": {"center": 40, "width": 80},
    "head": {"center": 90, "width": 350}
};

// namespace
var dwvsimple = dwvsimple || {};

dwvsimple.Gui = function (app) {
    // default selected index.
    var defaultSelectedIndex = 0;
    /**
     * Set the default selected index.
     * @param {number} index The value of the index.
     */
    this.SetDefaultSelectedIndex = function (index) {
        this.defaultSelectedIndex = index;
    };
    /**
     * Handle preset change.
     * @param {string} name The name of the new preset.
     */
    this.onChangePreset = function (name) {
        // update viewer
        app.onChangeWindowLevelPreset({currentTarget: {value: name}});
        // set selected
        this.setSelectedPreset(name);
    };
    /**
     * Handle tool change.
     * @param {string} name The name of the new tool.
     */
    this.onChangeTool = function (name) {
        app.onChangeTool({currentTarget: {value: name}});
    };
    /**
     * Get the DOM preset select element.
     */
    this.getDomPresets = function () {
        return app.getElement('presets');
    };
    /**
     * Handle display reset.
     */
    this.onDisplayReset = function () {
        app.onDisplayReset();
        // reset preset dropdown
        this.getDomPresets().selectedIndex = this.defaultSelectedIndex;
    };
}; // class dwvsimple.Gui

/**
 * Update preset dropdown.
 * @param {Array} presets The list of presets to use as options.
 */
dwvsimple.Gui.prototype.updatePresets = function (presets) {
    var domPresets = this.getDomPresets();
    // clear previous
    while (domPresets.hasChildNodes()) {
        domPresets.removeChild(domPresets.firstChild);
    }
    // add new
    for (var i = 0; i < presets.length; ++i) {
        var option = document.createElement('option');
        option.value = presets[i];
        var label = presets[i];
        var key = "wl.presets."+label+".name";
        if (dwv.i18nExists(key)) {
            label = dwv.i18n(key);
        }
        option.appendChild(document.createTextNode(label));
        domPresets.appendChild(option);
    }
};

/**
 * Set the selected preset in the preset dropdown.
 * @param {string} name The name of the preset to select.
 * @return {number} The index of the preset.
 */
dwvsimple.Gui.prototype.setSelectedPreset = function (name) {
    var domPresets = this.getDomPresets();
    // find the index
    var index = 0;
    for (index in domPresets.options) {
        if (domPresets.options[index].value === name) {
            break;
        }
    }
    // set selected
    domPresets.selectedIndex = index;
    return index;
};

function makeFullscreen(divId) {
    // check if user allows full screen of elements. This can be enabled or disabled
    // in browser config. By default its enabled.
    // its also used to check if browser supports full screen api.
    if ("fullscreenEnabled" in document ||
        "webkitFullscreenEnabled" in document ||
        "mozFullScreenEnabled" in document ||
        "msFullscreenEnabled" in document) {
        if (document.fullscreenEnabled ||
            document.webkitFullscreenEnabled ||
            document.mozFullScreenEnabled ||
            document.msFullscreenEnabled) {
            var element = document.getElementById(divId);
            //requestFullscreen is used to display an element in full screen mode.
            if("requestFullscreen" in element) {
                element.requestFullscreen();
            } else if ("webkitRequestFullscreen" in element) {
                element.webkitRequestFullscreen();
            } else if ("mozRequestFullScreen" in element) {
                element.mozRequestFullScreen();
            } else if ("msRequestFullscreen" in element) {
                element.msRequestFullscreen();
            }
        }
    } else {
        console.warn("User doesn't allow full screen.");
    }
}

function handleFullscreenExit(callback) {
    var onFullscreenChange = function () {
        // when exiting full screen
        if (!document.fullScreenElement &&
            !document.webkitFullScreenElement &&
            !document.mozFullScreenElement &&
            !document.msFullScreenElement) {
            callback();
        }
    };
    document.addEventListener("fullscreenchange", function (/*event*/) {
        onFullscreenChange();
    });
    document.addEventListener("webkitfullscreenchange", function (/*event*/) {
        onFullscreenChange();
    });
    document.addEventListener("mozfullscreenchange", function (/*event*/) {
        onFullscreenChange();
    });
    document.addEventListener("msfullscreenchange", function (/*event*/) {
        onFullscreenChange();
    });
}
