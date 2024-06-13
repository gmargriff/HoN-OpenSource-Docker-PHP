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
     win.openDevTools();
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
    let userlogin_file = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game", "login.cfg");
    if (!fs.existsSync(userlogin_file)) {
        fs.mkdirSync(path.dirname(userlogin_file), { recursive: true });
    }
    let logincfg = `// *** DO NOT EVER SHARE THIS FILE WITH ANYONE *** \n// *** STAFF MEMBERS WILL NOT ASK FOR THIS FILE *** \n// *** EVEN THOUGH YOUR PASSWORD IS NOT VISIBLE *** \n// *** THIS INFORMATION CAN BE USED TO STEAL YOUR ACCOUNT *** \nlogin_rememberName 1\nlogin_name ${params.username}\nlogin_rememberPassword 1\nlogin_password ${params.hash}`
    fs.writeFileSync(userlogin_file, logincfg);
    
    let parameters = false;
    let userlogin_folder = path.join(app.getPath("documents"), "Heroes of Newerth x64", "game");

    if (!fs.existsSync(userlogin_folder)) {
        fs.mkdirSync(path.dirname(userlogin_folder), { recursive: true });
    }

    if (fs.existsSync(path.join(userlogin_folder, "user.cfg"))) {
        parameters = JSON.parse(fs.readFileSync(path.join(userlogin_folder, "user.cfg"), { encoding: "utf8" }))
    }

    if (!parameters) {
        parameters = {
            WINE_PATH: os.platform() !== "win32" && commandExistsSync('wine') ? "wine" : false,
            WINEPREFIX: false,
            HON_EXE: false,
            MASTERSERVER: "192.168.100.6:8080"
        }
    }

    if (os.platform() !== "win32") {
        while (!parameters.WINE_PATH) {
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
            parameters.WINEPREFIX = dialog.showOpenDialogSync({
                properties: ['openDirectory']
            })
            if (parameters.WINEPREFIX) {
                parameters.WINEPREFIX = parameters.WINEPREFIX[0];
            }
        }
    }

    while (!parameters.HON_EXE || !fs.existsSync(parameters.HON_EXE)) {
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

    fs.writeFileSync(path.join(userlogin_folder, "user.cfg"), JSON.stringify(parameters));

    let loadClient = "";
    if (parameters.WINEPREFIX) {
        loadClient = `WINEPREFIX="${parameters.WINEPREFIX}" ${parameters.WINE_PATH} "${parameters.HON_EXE}" -masterserver ${parameters.MASTERSERVER}`;
    } else {
        loadClient = `"${parameters.HON_EXE}" -masterserver ${parameters.MASTERSERVER}`;
    }
    exec(loadClient);
});