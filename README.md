# Project info

## Brief overview
This is an application which allows users learner drivers to speed up fast track their driving test appointment to a couple of weeks instead of a couple of months. 

## Some Technical Details
It works by checking the dvla website every minute or so (depending on the configured interval) and managing the slot appointments. It will decide which candidate is most suited to the given slot based on a number of factors - prefered driving test location, user prioirty (based on plan level) and signed up date. The user can then decide to take the slot or leave it, at which point the application will find the next best candidate and offer them the slot. The process will repeat untill either someone takes the slot or no appropriate canidates accept, at which point the slot is discarded. This functionality is entriely queue driven (with the exception of the user accepting the slot). 

## Some Main Features
It uses SMS messaging (including replies), browser automation (using a repurposed version of Laravel Dusk which can be found openly available in my GitHub repo), proxying, stripe payments, and a large queue system using Laravel Horizon. 

## What's included

Some of the main project files include

* app/modules
    * Some traits and helper classes
* app/Jobs/Tasks
    * Main queue controllers
* app/Models
    * Models 
* app/Controllers
    * Controllers
* app/Notifications

The app is mainly backend focused so not much front-end magic going on. My video sharing app might be a better place to look at for this :).
P.S. this code is not much commented and there may be a lot of "WIP"s going on in the git history. I realise the negatives of this although, as this was my own project and wasn't expecting anyone to ever be looking through, I was lazy :/.

Below are some of my notes for when building the app... Not much of interest.

## Personal notes

The business logic is broken into groups

* Crawling logic
* Data processing logic
* Customer/slot management logic
* Queue logic

## Main design objective

Allow each of the above to be operated independantly 

Substitute crawling logic for testing of application

Start from data processing

# Add cron task
# crontab -e
# * * * * * cd /home/vagrant/Dev/laravel-web-crawler && php artisan schedule:run >> /dev/null 2>&1

- php artisan config:clear
- php artisan config:cache
- php artisan horizon
