# Photo Albums plugin

This is [OctoberCMS](http://octobercms.com) plugin allowing to create, edit and display photos arranged in albums. Each Photo is a model with image attached to it.
And Album is an another model, owning multiple of Photos. 

The aim of this approach is to treat each photo as a separate entity which can be displayed separately, have it's own title, description, could have comments of its own etc. 
And at the same time, photos are grouped in albums and can be displayed on album's page with pagination.

Also now you can insert photos from galleries right into the blog posts (see below).

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
Image thumb is generated from selected front photo which you can set on album's edit page in the photos list (check the photo, click "Set as front" button). 
If no photo is selected is front, the latest uploaded photo will be used for thumb.

### Random Photos

Displays given number of random photos. Note that for big database tables, selects with random sorting can slow down your site, so use the component with caution and make use of cache lifetime to avoid running the query on each component show. Also note that due to the use of RAND() function for sorting, the component would work with MySQL and Sqlite databases only. To use the component with other databases, you'd need to rewrite orderBy() call, otherwise it will just return non-random collection. After October updates to Laravel 5.5, DB-independent function will be used.

## Uploading

At the moment, there are 3 ways to upload photos:

* Add single photo using the New photo form
* Add single photo using relations manager when in album update form
* Add multiple photos to an album from the Upload photos form

Uploading multiple photos is supported with the [Dropzone.js](http://www.dropzonejs.com/) plugin. You don't need to install it as it is already a part of October.

## Insert photos from galleries

### Dialog to insert photos into Blog posts

You can insert photos from galleries created by this plugin into [Blog](https://octobercms.com/plugin/rainlab-blog) posts. 
Just click on a camera icon near media manager in the post markdown editor, then select album and photo.  Markdown code for selected photo will appear in the editor.

### Markdown syntax

To change the code template, go to Settings -> Photo Albums tab. The syntax is explained below and you can use `%id%` and `%title%` placeholders for photo id and title. 
You can use placeholders multiple times. For example, you can type in template like this:
 
```[![%title%]([photo:%id%:640:480:crop]){.img-responsive}]([photo:%id%]  &quot;%title%&quot;){.magnific}```

It will result in image 640x480 cropped thumb with title having `img-responsive` class, linked to full-size image with title and `magnific` class. 
Note that you can't use quote symbol in the template, you have to replace quotes with `&quot;`.

The syntax for [photo] part is as follows:

```[photo:id:width:height:mode]```

Here:
* `id` is a photo model id (you can get it from url).
* `width` and `height` are optional, if they are provided, photo will be inserted as a thumbnail with these width and height.
* `mode` is an optional mode for thumbnail generation, possible values are: `auto`, `exact`, `portrait`, `landscape`, `crop` (see October thumbs generation for more info). Defaults to `auto`.
 
For example: 

* `[photo:123:640:480:crop]` for cropped thumbnail 640x480 of photo with id 123
* `[photo:123:200:200]` for thumbnail 200x200 of photo with id 123
* `[photo:123]` for image as is, no thumb

The placeholder will be replaced with path to image (or thumb), for example: `/storage/app/uploads/public/57a/24e/bff/thumb_301_640x480_0_0_auto.jpg`.

You can use this code to insert photos in any markdown-processed text.

Note that to avoid possible conflicts, placeholders are only replaced inside `src=""` and `href=""` clauses. 
So if you add placeholder in href for anchor tag or in src for img tag (or into Markdown link or image), it will be replaced. And if you add it into plain text, it will be ignored.

## Roadmap

### Photo insert selection dialog

To make photo insert more comfortable, it would be nice to have editor button showing dialog for album/photo selection generating placeholders automatically (just like Media insert button).

### Attachments location

Right now plugin uses System\Models\File to attach images so they are stored in system uploads, each one in separate directory with random names. 
It could be nice to put them in one directory per album.

### Categories support for albums

It would be nice to be able to separate albums by categories, to group them by categories in the AlbumsList component etc.
