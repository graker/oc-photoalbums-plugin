# Photo Albums plugin

This is [OctoberCMS](http://octobercms.com) plugin allowing to create, edit and display photos arranged in albums. Each Photo is a model with image attached to it.
And Album is an another model, owning multiple of Photos. 
The aim of this approach is to treat each photo as a separate entity which can be displayed separately, have it's own title, description, could have comments of its own etc. 
And at the same time, photos are grouped in albums and can be displayed on album's page with pagination.

## Components

There are 4 components in the plugin: Photo, Album, Albums List and Random Photos.

### Photo

Photo component should be used to output a single photo. Data available for this single photo:
 
* photo's title and description
* photo's created date
* image path
* parent album's title and url
* mini-navigator to go to the previous or the next photo

### Album

This component is used to output album's photos. Data available:

* album's title and description
* each photo's title, thumb and url
* pagination

### Albums list

Use this component to output all albums (pagination is supported). For each album you can output title, image thumb and photos count.

### Random Photos

Displays given number of random photos.

## Uploading

At the moment, there are 3 ways to upload photos:

* Add single photo using the New photo form
* Add single photo using relations manager when in album update form
* Add multiple photos to an album from the Upload photos form

Uploading multiple photos is supported with the [Dropzone.js](http://www.dropzonejs.com/) plugin. You don't need to install it as it is already a part of October.

## Roadmap

### Attachments location

Right now plugin uses System\Models\File to attach images so they are stored in system uploads, each one in separate directory with random names. 
It could be nice to put them in one directory per album.

### Categories support for albums

It would be nice to be able to separate albums by categories, to group them by categories in the AlbumsList component etc.

### Photos reordering

On album's page photos are sorted by creation date (desc). Need to add some reordering abilities.

### Ajax support (for photo page rendering and navigation)

This one is not a priority right now.
