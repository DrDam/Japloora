# Japloora 
## Just Another PHP Lightweight Object Oriented RestAPI

* [Introduction](#introduction)
* [Purpose](#Purpose)
* [Get started](#get-started)
* [Identification](#identification)

### Introduction

Japloora are a try to make a little API with REST-FULL capacity.

### Purpose

The way to use are simple : 
- A controler defined route caracteristics ( path, scheme, method, HTML parameters, callback, output format) and implement callback method responding to Request
- A controler can defined multiple route
- Route can be "wildcarded" ( "get/type/*" )
- A controler can precise a "wildcarded" route of another controler
- Using a basic and "light" Authent mecanisme


### Get started

- Get package with composer(japloora/japloora ),
- Copy "exemple" folder's files on to the Apache DocumentRoot.
- Add Apache read/write on "AuthentDB" and "init" folder
- In init Folder, edit init.yml change login/password of Authent Super Admin
- Try exemple URLs

### Identification

Japloora not make Authentification, it only make identification.  
That mean a user can be identify via a JWT token composed with :

- header almgorithme declaration : "HS256",
- payload : 
    - "sub" parameter : site_key 
    - "use" parameter : my_username
    - "iat" parameter : timestamp in token creation
- secret key are a SHA256 encrytption of 
    - SHA256 encrytpted user_password concatenated with timestamp ( 'iat' paremeter)

A token are valid for only 15min. It's recommanded gerenate a new on each call
Parameters ( site_key, username and user_password ) are exchange out-of-line and the password where never send from client to server