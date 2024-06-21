
# Heroes of Newerth - Minimal Server 

As the title says, this is a Heroes of Newerth **minimal** server, which means this project will be able to do whatever I want it to do, and won't be able to do whatever I don't want it to do. So, to make things clearer, here's what is working, what is not working, and what I don't intend on make to work (but, of course, you're allowed to do it if you want):

---
**Currently working:**

 - [x] Account registration
 - [x] Account authentication (Login)
 - [x] Playing VS Bots and friends over LAN
 - [x] Alt avatar shopping (parcial)
 - [x] Getting a few silver coins for every match played
 - [x] New client which allow easily logging in and playing on local and private servers
 - [x] Hero Guides

**Intended, but not working:**

 - [ ] New client should track when game is open and hide / show play button as needed
 - [ ] New client should detect and a match is currently ongoing
 - [ ] Shopping alt avatars immediate effect
 - [ ] Allow buying alt avatars on char select
 - [ ] Keep track of player progress (achievements, seasons, rank...)

**Unnintended (not working, can work, but I won't make it work)**

❌ Can't play online / ranked matches
❌ Can't buy other item types from shop (we have a list from every item in one of the folders, you can work it out if you want)
❌ Can't create / join clans
❌ [Insert here anything else that was not listed on the other two categories]

---
As you can see, I do not intend to write a whole server with functional chatserver and full store. I only want to play this game mostly singleplayer or with close friends with a minimal amount of work while still keeping some sort of progression system. This means I'm going to make an effort to write a new client where I can implement new stuff that will work as long as it's running as the game goes.

## Requirements
To make this server work you'll need only these things:

-   [Docker](https://docs.docker.com/get-docker/)
-   [Basic Docker Knowledge](https://docs.docker.com/guides/get-started/)
-   [Git](https://git-scm.com/downloads) (Optional)
- A Heroes of Newerth installation (to play the game)

If you have no Docker Knowledge, please, open [this link](https://docs.docker.com/guides/walkthroughs/what-is-a-container/) and follow each step until you reach, at least, [Multi-container Apps](https://docs.docker.com/guides/walkthroughs/multi-container-apps/). **It should take you less than 30 minutes** to follow through and you'll be more prepared to any problem that you might find.

## Great, I've read everything, now how do I make it work????

### Step 1 - Downloading required files

You may want to clone this repository as it will be easier to keep it updated if I make any change.

```
git clone https://github.com/gmargriff/HoN-OpenSource-Docker-PHP.git
```

If you cannot use git or just don't want to, just download the ZIP file from [this link](https://github.com/gmargriff/HoN-OpenSource-Docker-PHP/archive/refs/heads/main.zip)

### Step 2 - Setting up config files

First thing to do is prepare your environment file. There's a file named `.env.example` in the main folder from this repo. Make a copy in the same folder and name it `.env`. For starters, the default config is enough to keep your server running, but as you explore you might want to add or change as you go.

### Step 3 - Building server binaries

If you have [Basic Docker Knowledge](https://docs.docker.com/guides/get-started/), you might have found our `docker-compose.yml` and `Dockerfile` already. So, now, it's time to build your container image containing the compiled server binaries.

For that, use your favorite terminal emulator (powershell, bash, zsh, fish, anything) to navigate to the root folder of our project and run:

```
docker compose build
```

This might take a few minutes, so, be patient.

### Step 4 - Starting our server

Now, you just have to execute your server:

```
docker compose up -d
```

This might take a few time too.

### Step 5 - Installing dependencies
Now, you need to install PHP Composer dependencies inside the PHP container. To do so, run the following command:

```
docker compose exec php composer install --working-dir="/var/www/html/public"
```

### Step 6 - Fix file permissions

Sometimes, when cloning the repos, some important folders have trouble setting up permissions. To fix this, run the following commands:
```
docker compose exec php chmod 777 -R /var/www/html/public/public_docs && \
docker compose exec phpmyadmin chmod 777 -R /sessions
```
### Step 7 - Building new client binaries (optional)
So, right now you're already able to play using a clean game install. If that's what you want, just skip to the **Register, connect and play** section.

But, if you want to play solo and want to have some kind of progression, you'll need to play with the electron client. To build it, run the following command:
```
docker compose up build_client
```

You'll find your new binaries for Windows, Mac and Linux inside the `public/ElectronClient/out`folder.

## Register, connect and play
Okay, now that you're done building stuff, it's time to play.
First, open your browser and connect to `http://127.0.0.1:8080`.
You'll find the registration page, create your new account and, if you want, put a .zip file named `hon_client.zip` containing the game installation so others can download from this page.

Now, you can open your game and login with your account.

**If you're playing with the new client:**
You'll be able to login with the new client and play straight. It may ask you to point where to put the WINEPREFIX (if you're on linux or mac) and where to find the `hon_x64.exe`  (from your installation). If you ever want to change the IP address you're connecting with the new client, go to your Documents folder, `Documents > Heroes of Newerth x64 > game > user.cfg` and change the MasterServer IP address.

Keep it open while you play and it'll automatically submit your bot matches to the server to get you some silver everytime you play, which you can use to buy alt avatars in shop.

**If you're playing with the default client**
Follow the [/u/crazy_salami](https://www.reddit.com/user/crazy_salami/) guide from [this link](https://www.reddit.com/r/HeroesofNewerth/comments/wj5kyd/guide_to_playing_hon_on_private_servers/) changing the Kongor link to your own.

> Find your HoN executable (aka _hon_x64.exe_).
> 
> Right click and create shortcut.
> 
> Now right click the shortcut and click _Properties_.
> 
> Select the _Shortcut_ tab.
> 
> The _Target_ field is probably already highlighted
> 
> At the end of the _Target_ field add the following: **-masterserver
> 127.0.0.1:8080**
> 
> Once you're done, your _Target_ field should probably look something
> like this:
> 
> "C:\Program Files\Heroes of Newerth x64\hon_x64.exe" **-masterserver
> 127.0.0.1:8080**
> 
> **You're done setting up HoN!**
> 
> Now you can login using an _available_ username that you register at
> 127.0.0.1:8080.

## Credits

I'd like to use this space to say thanks to everyone that has been contributing to HoN Private Server scene, specially the ones mentioned in the `.env.example` file for all their hard work.

This project would never exist if it were not for them.

Thanks to Anton Romanov aka Theli, Denis Koroskin aka korDen, Shawn Presser, Xen0byte, mrhappyasthma and any other Project Kongor contributor.

https://kongor.online/
https://github.com/Project-KONGOR-Open-Source/NEXUS
