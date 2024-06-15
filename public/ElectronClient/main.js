const { app, BrowserWindow, ipcMain, shell, dialog } = require('electron')
const path = require("node:path");
const fs = require('node:fs');
const os = require('node:os');
const exec = require('child_process').exec;
const commandExistsSync = require('command-exists').sync;

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

ipcMain.handle("openHoNRegister", () => {
    shell.openExternal("http://192.168.100.6:8080");
});

ipcMain.handle("openHonClient", (e, params) => {
    // Create variable to keep parameters for game launch
    let parameters = false;

    // Get the current environment documents folder and check if HoN folder exists.
    // Creates if it doesn't
    let userlogin_folder = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game");

    if (!fs.existsSync(userlogin_folder)) {
        fs.mkdirSync(path.dirname(userlogin_folder), { recursive: true });
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
            MASTERSERVER: "192.168.100.6:8080"
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
        let prefixlogin_file = path.join(parameters.WINEPREFIX, "drive_c", "users", "steamuser", "Documents", "Heroes of Newerth x64", "game", "login.cfg");
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