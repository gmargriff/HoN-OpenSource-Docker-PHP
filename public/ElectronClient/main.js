const { app, BrowserWindow, ipcMain, shell, dialog, webContents } = require('electron')
const path = require("node:path");
const fs = require('node:fs');
const os = require('node:os');
const { default: axios } = require('axios');
const exec = require('child_process').exec;
const commandExistsSync = require('command-exists').sync;
require('@electron/remote/main').initialize()

const createWindow = () => {
    const win = new BrowserWindow({
        width: 1280,
        height: 720,
        titleBarStyle: 'hidden',
        autoHideMenuBar: true,
        frame: false,
        webPreferences: {
            contextIsolation: true,
            nodeIntegration: true,
            preload: path.join(__dirname, "./preload.js"),
            enableRemoteModule: true
        }
    })
    win.setMenu(null);
    // win.openDevTools();
    win.loadFile('index.html');
    win.setIcon(path.join(__dirname, '/icons/icon.png'));
}

app.whenReady().then(() => {
    createWindow()

    app.on('window-all-closed', () => {
        if (process.platform !== 'darwin') app.quit()
    })

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) createWindow()
    })
});

app.on('browser-window-created', (_, window) => {
    require("@electron/remote/main").enable(window.webContents)
})

ipcMain.handle("openHoNRegister", () => {
    let usercfg = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game", "user.cfg");
    let parameters = JSON.parse(fs.readFileSync(usercfg, { encoding: "utf8" }))
    shell.openExternal(`http://${parameters.MASTERSERVER}`);
});

ipcMain.handle("submitGameLogs", () => {
    // Create variable to keep parameters for game launch
    let parameters = false;

    // Get the current environment documents folder and check if HoN folder exists.
    // Creates if it doesn't
    let userlogin_folder = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game");

    if (!fs.existsSync(userlogin_folder)) {
        fs.mkdirSync(userlogin_folder, { recursive: true });
    }

    // Check if a user.cfg file exists in HoN folder and load it's parameters it it does
    if (fs.existsSync(path.join(userlogin_folder, "user.cfg"))) {
        parameters = JSON.parse(fs.readFileSync(path.join(userlogin_folder, "user.cfg"), { encoding: "utf8" }))
    }

    // If no parameters have been set, create a new config
    if (!parameters) {
        parameters = {
            WINEPREFIX: false
        }
    }

    let logs_folder = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game", "logs");

    if (parameters.WINEPREFIX) {
        // If user not on windows, change logs_folder to inside wineprefix
        
        // Get user username
        const username = os.userInfo().username;

        // Get user logs folder
        logs_folder = path.join(parameters.WINEPREFIX, "drive_c", "users", username, "Documents", "Heroes of Newerth x64", "game", "logs");
    }

    // If folder does not exist, create it
    if (!fs.existsSync(logs_folder)) {
        fs.mkdirSync(logs_folder, { recursive: true });
    }

    // Read directory to check if there are game logs
    fs.readdir(logs_folder, (err, files) => {
        if(!files) {
            return;
        }
        // Run through each file in folder
        files.forEach(file => {
            // If it starts with "game_", try to read it
            if (file.startsWith("game_")) {
                if (fs.existsSync(path.join(logs_folder, file))) {
                    let file_content = fs.readFileSync(path.join(logs_folder, file), { encoding: "ucs2" });
                    let game_info = {
                        start: Date.now(),
                        players: [],
                        mode: "",
                        time: "",
                        readable_time: "",
                        winner: false
                    }
                    file_content.toString().split('\n').forEach((line) => {
                        // Process game start date
                        if (line.includes("INFO_DATE")) {
                            let datetime = line.split(`"`);
                            let date = datetime[1].split(`/`);
                            date = `${date[0]}/${date[2]}/${date[1]}`;
                            let time = datetime[3];
                            game_info.start = Date.parse(`${date} ${time}`);
                        }
                        // Process players connect to game
                        if (line.includes("PLAYER_CONNECT")) {
                            let pl = line.split(`"`);
                            pl = {
                                "order": parseInt(pl[0].replace("PLAYER_CONNECT player:", "").replace(" name:", "")),
                                "user": pl[1],
                                "team": false
                            }
                            game_info.players.push(pl);
                        }
                        // Add player to correct team
                        if (line.includes("PLAYER_TEAM_CHANGE")) {
                            // teamChange is an array of two indexes, 0 is player identifier, 1 is the team it has joined
                            let teamChange = line.replace("PLAYER_TEAM_CHANGE player:", "").replace("team:", "").split(" ");

                            // Get the desired player object from players array
                            let changePlayer = game_info.players.find((p) => {
                                return parseInt(p.order) == parseInt(teamChange[0])
                            });

                            // Get player index if player was found, or skip if it wasn't
                            if (changePlayer) {
                                changePlayer = game_info.players.indexOf(changePlayer);
                                // changePlayer is now the array index from current player,
                                // so we can change it's team as needed
                                game_info.players[changePlayer].team = parseInt(teamChange[1]);
                            }
                        }
                        // Get game mode
                        if (line.includes("INFO_SETTINGS")) {
                            let gmode = line.split(`"`);
                            game_info.mode = gmode[1];
                        }
                        if (line.includes("GAME_END")) {
                            // game_end is an array with the following indexes:
                            // 0 => GAME_END string
                            // 1 => time:current game time
                            // 2 => winner:"identifier of winning team"
                            let game_end = line.split(" ");

                            // Store time spent in game to game_info.tine
                            game_info.time = game_end[1].replace("time:", "");
                            game_info.readable_time = new Date(parseInt(game_info.time)).toISOString().slice(11, 19);

                            // Store game winning team identifier to game_info.winner
                            game_info.winner = parseInt(game_end[2].replace(`winner:`, "").replace(`\r`, "").replace(`"`, ""));
                        }
                    })

                    // If file is older than 3 hours and has no winner, remove it
                    let timestamp = Date.now();
                    if (game_info.start <= (Date.now() - (1000 * 60 * 60 * 3)) && !game_info.winner) {
                        fs.renameSync(path.join(logs_folder, file), path.join(logs_folder, `removed_${timestamp}_${file}`));
                    }

                    // If game has winner, submit to server
                    if (game_info.winner) {
                        let form_data = new FormData();
                        form_data.append("game", JSON.stringify(game_info));
                        form_data.append("f", "game_logs");
                        axios.post(`http://${parameters.MASTERSERVER}/client_requester.php`, form_data).then(response => {
                            if (parseInt(response.data) === 200) {
                                fs.renameSync(path.join(logs_folder, file), path.join(logs_folder, `sent_${timestamp}_${file}`));
                            }
                        });
                    }
                }
            }
        });
    });
});

ipcMain.handle("openHonClient", (e, params) => {
    // Create variable to keep parameters for game launch
    let parameters = false;

    // Get the current environment documents folder and check if HoN folder exists.
    // Creates if it doesn't
    let userlogin_folder = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game");

    if (!fs.existsSync(userlogin_folder)) {
        fs.mkdirSync(userlogin_folder, { recursive: true });
    }

    // Check if a user.cfg file exists in HoN folder and load it's parameters it it does
    if (fs.existsSync(path.join(userlogin_folder, "user.cfg"))) {
        parameters = JSON.parse(fs.readFileSync(path.join(userlogin_folder, "user.cfg"), { encoding: "utf8" }))
    }

    // If no parameters have been set, create a new config
    if (!parameters) {
        parameters = {
            WINE_PATH: os.platform() !== "win32" && commandExistsSync('wine') ? "wine" : false,
            WINEPREFIX: false,
            HON_EXE: false,
            MASTERSERVER: false
        }
    }

    // If user is not on windows, ask for wine path and WINEPREFIX for HoN
    if (os.platform() !== "win32") {
        while (!parameters.WINE_PATH) {
            dialog.showMessageBoxSync(options = {
                message: "Select a valid wine binary",
                type: "info",
                buttons: ["Open"],
                title: "Select Wine",
                noLink: true
            })
            parameters.WINE_PATH = dialog.showOpenDialogSync({
                properties: ['openFile']
            })
            if (parameters.WINE_PATH) {
                parameters.WINE_PATH = parameters.WINE_PATH[0];
                if (!fs.existsSync(parameters.WINE_PATH)) {
                    parameters.WINE_PATH = false;
                }
            }
        }

        while (!parameters.WINEPREFIX || !fs.existsSync(parameters.WINEPREFIX)) {
            dialog.showMessageBoxSync(options = {
                message: "Select a valid path for Wine Prefix",
                type: "info",
                buttons: ["Open"],
                title: "Select WINEPREFIX",
                noLink: true
            })
            parameters.WINEPREFIX = dialog.showOpenDialogSync({
                properties: ['openDirectory']
            })
            if (parameters.WINEPREFIX) {
                parameters.WINEPREFIX = parameters.WINEPREFIX[0];
            }
        }
    }

    // Asks for HoN executable
    while (!parameters.HON_EXE || !fs.existsSync(parameters.HON_EXE)) {
        dialog.showMessageBoxSync(options = {
            message: "Please, select your hon_x64.exe file",
            type: "info",
            buttons: ["Open"],
            title: "Select hon_x64.exe",
            noLink: true
        })
        parameters.HON_EXE = dialog.showOpenDialogSync({
            properties: ['openFile'],
            filters: [
                { name: 'hon_x64.exe', extensions: ['exe'] },
            ]
        })
        if (parameters.HON_EXE) {
            parameters.HON_EXE = parameters.HON_EXE[0];
        }
    }


    // Create a config file for current user auto login on game
    let userlogin_file = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game", "login.cfg");
    if (!fs.existsSync(userlogin_file)) {
        fs.mkdirSync(path.dirname(userlogin_file), { recursive: true });
    }
    let logincfg = `// *** DO NOT EVER SHARE THIS FILE WITH ANYONE *** \n// *** STAFF MEMBERS WILL NOT ASK FOR THIS FILE *** \n// *** EVEN THOUGH YOUR PASSWORD IS NOT VISIBLE *** \n// *** THIS INFORMATION CAN BE USED TO STEAL YOUR ACCOUNT *** \nlogin_rememberName 1\nlogin_name ${params.username}\nlogin_rememberPassword 1\nlogin_password ${params.hash}`
    fs.writeFileSync(userlogin_file, logincfg);

    // Writes the current configs to user.cfg
    fs.writeFileSync(path.join(userlogin_folder, "user.cfg"), JSON.stringify(parameters));

    // Start game
    let loadClient = "";
    if (parameters.WINEPREFIX) {
        // If user not on windows, creates login.cfg inside WINEPREFIX
        // Get user username
        const username = os.userInfo().username;
        let prefixlogin_file = path.join(parameters.WINEPREFIX, "drive_c", "users", username, "Documents", "Heroes of Newerth x64", "game", "login.cfg");
        if (!fs.existsSync(prefixlogin_file)) {
            fs.mkdirSync(path.dirname(prefixlogin_file), { recursive: true });
        }
        fs.writeFileSync(prefixlogin_file, logincfg);
        loadClient = `WINEPREFIX="${parameters.WINEPREFIX}" ${parameters.WINE_PATH} "${parameters.HON_EXE}" -masterserver ${parameters.MASTERSERVER}`;
    } else {
        loadClient = `"${parameters.HON_EXE}" -masterserver ${parameters.MASTERSERVER}`;
    }
    exec(loadClient);
});