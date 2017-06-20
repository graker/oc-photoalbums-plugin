
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
            extraData: {album: this.options.album },
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
        this.$dialog = popup;
        // bind clicks for album thumb and title links
        if (this.options.album) {
            this.bindPhotosListHandlers();
        } else {
            $('#albumsList .album-link', popup).one('click', this.proxy(this.onAlbumClicked));
        }
        $('div.photo-selection-dialog').find('button.btn-insert').click(this.proxy(this.onInsertClicked));
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
            loading: $.oc.stripeLoadIndicator,
            success: function (data) {
                this.success(data);
                selector.bindPhotosListHandlers();
            }
        });
    };


    /**
     * Bind event handlers for photos list
     */
    PhotoSelector.prototype.bindPhotosListHandlers = function () {
        // bind photo link click and double click events
        $('#photosList').find('a.photo-link').click(this.proxy(this.onPhotoSelected));
        $('#photosList').find('a.photo-link').dblclick(this.proxy(this.onPhotoDoubleClicked));
        // bind back to albums click event
        $('#photosList').find('a.back-to-albums').one('click', this.proxy(this.onBackToAlbums));
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
            loading: $.oc.stripeLoadIndicator,
            success: function (data) {
                this.success(data);
                $('#albumsList').find('.album-link').one('click', selector.proxy(selector.onAlbumClicked));
            }
        });
    };


    /**
     * Photo insert button callback
     *
     * @param event
     */
    PhotoSelector.prototype.onInsertClicked = function (event) {
        var selected = $('#photosList').find('a.selected').first();
        if (!selected.length) {
            alert('You have to select a photo first. Click on the photo, then click "Insert". Or just double-click the photo.');
        } else {
            var code = selected.data('request-data');
            var album = $('#photosList').data('request-data');
            this.options.onInsert.call(this, code, album);
        }
    };


    /**
     *
     * Double click callback
     *
     * @param event
     */
    PhotoSelector.prototype.onPhotoDoubleClicked = function (event) {
        // select the photo and insert it
        var link = $(event.currentTarget);
        link.trigger('click');
        $('div.photo-selection-dialog').find('button.btn-insert').trigger('click');
    };


    /**
     * Hide popup
     */
    PhotoSelector.prototype.hide = function () {
        if (this.$dialog) {
            this.$dialog.trigger('close.oc.popup');
        }
    };


    /**
     * Default options
     */
    PhotoSelector.DEFAULTS = {
        alias: undefined,
        album: 0,
        onInsert: undefined
    };

    $.oc.photoselector.popup = PhotoSelector;

} (window.jQuery);
