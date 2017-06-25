# Japloora 
## Just Another PHP Lightweight Object Oriented RestAPI

* [Introduction](#introduction)
* [Purpose](#Purpose)
* [Get started](#get-started)


### Introduction

Japloora are a try to make a little API with REST-FULL capacity.

### Purpose

The way to use are simple : 
- A controler defined route caracteristics ( path, scheme, method, HTML parameters, callback, output format) and implement callback method responding to Request
- A controler can defined multiple route
- Route can be "wildcarded" ( "get/type/*" )
- A controler can precise a "wildcarded" route of another controler


### Get started

- Get package with composer(japloora/japloora ),
- Copy "exemple" folder's files on to the Apache DocumentRoot.
- Add Apache read/write on "AuthentDB" and "init" folder
- In init Folder, edit init.yml change login/password of Authent Super Admin
- Try exemple URLs