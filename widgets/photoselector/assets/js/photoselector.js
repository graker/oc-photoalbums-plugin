
/**
 * PhotoSelector dialog
 */

+function () {

    if ($.oc.photoselector === undefined) {
        $.oc.photoselector = {};
    }

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype;

    var PhotoSelector = function (options) {
        this.$dialog = $('<div/>');
        this.options = $.extend({}, PhotoSelector.DEFAULTS, options);

        Base.call(this);

        this.show();
    };


    PhotoSelector.prototype = Object.create(BaseProto);
    PhotoSelector.prototype.constructor = PhotoSelector;


    /**
     * Load and show the dialog
     */
    PhotoSelector.prototype.show = function () {
        this.$dialog.one('complete.oc.popup', this.proxy(this.onPopupShown));
        this.$dialog.popup({
            size: 'large',
            handler: this.options.alias + '::onDialogOpen'
        });
    };


    /**
     * Callback when the popup is loaded and shown
     *
     * @param event
     * @param element
     * @param popup
     */
    PhotoSelector.prototype.onPopupShown = function (event, element, popup) {
        // bind clicks for album thumb and title links
        $('#albumsList .album-link', popup).one('click', this.proxy(this.onAlbumClicked));
    };


    /**
     * Album clicked processor
     * @param event
     */
    PhotoSelector.prototype.onAlbumClicked = function (event) {
        var link_id = $(event.currentTarget).data('request-data');
        $.request('onAlbumLoad', {
            data: {id: link_id},
            update: {photos: '#albumsList'}
        });
    };


    /**
     * Default options
     */
    PhotoSelector.DEFAULTS = {
        alias: undefined,
        onInsert: undefined
    };

    $.oc.photoselector.popup = PhotoSelector;

} (window.jQuery);
