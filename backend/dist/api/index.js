"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const path_1 = __importDefault(require("path"));
let app;
try {
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    const mainApp = require(path_1.default.join(__dirname, '../src/index'));
    app = mainApp.default || mainApp;
}
catch (error) {
    console.error('Failed to load main app:', error);
    throw error;
}
exports.default = app;
