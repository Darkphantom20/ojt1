"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const express_1 = __importDefault(require("express"));
const cors_1 = __importDefault(require("cors"));
const dotenv_1 = __importDefault(require("dotenv"));
const auth_1 = __importDefault(require("./routes/auth"));
const student_1 = __importDefault(require("./routes/student"));
const admin_1 = __importDefault(require("./routes/admin"));
const coordinator_1 = __importDefault(require("./routes/coordinator"));
dotenv_1.default.config();
const app = (0, express_1.default)();
const port = process.env.PORT || 4000;
app.use((0, cors_1.default)({ origin: process.env.FRONTEND_URL || 'http://localhost:3000', credentials: true }));
app.use(express_1.default.json());
app.use('/api/auth', auth_1.default);
app.use('/api/students', student_1.default);
app.use('/api/admin', admin_1.default);
app.use('/api/coordinator', coordinator_1.default);
app.get('/api/health', (_req, res) => {
    res.json({ status: 'ok' });
});
app.listen(port, () => {
    console.log(`Backend running on http://localhost:${port}`);
});
