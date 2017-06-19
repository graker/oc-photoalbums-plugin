
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
     * Album clicked callback
     * @param event
     */
    PhotoSelector.prototype.onAlbumClicked = function (event) {
        var link_id = $(event.currentTarget).data('request-data');
        var selector = this;
        $.request('onAlbumLoad', {
            data: {id: link_id},
            update: {photos: '#albumsList'},
            success: function (data) {
                this.success(data);
                // bind photo link clicks event
                $('#photosList').find('a.photo-link').click(selector.proxy(selector.onPhotoSelected));
                // bind back to albums click event
                $('#photosList').find('a.back-to-albums').one('click', selector.proxy(selector.onBackToAlbums));
            }
        });
    };


    /**
     *
     * Photo clicked callback
     *
     * @param event
     */
    PhotoSelector.prototype.onPhotoSelected = function (event) {
        // remove old selected classes
        $('#photosList').find('a.selected').removeClass('selected');

        // add new selected classes
        var wrapper = $(event.currentTarget).parents('.photo-links-wrapper');
        wrapper.find('a.photo-link').addClass('selected');
    };


    /**
     *
     * Back to albums clicked callback
     *
     * @param event
     */
    PhotoSelector.prototype.onBackToAlbums = function (event) {
        var selector = this;
        $.request('onAlbumListLoad', {
           'update': { albums: '#photosList'},
            success: function (data) {
                this.success(data);
                $('#albumsList').find('.album-link').one('click', selector.proxy(selector.onAlbumClicked));
            }
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
