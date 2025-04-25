const fs = require("fs");
const bcrypt = require("bcryptjs");
const puppeteer = require("puppeteer");
const mongoose = require("mongoose");
const { toMatchImageSnapshot } = require("jest-image-snapshot");
expect.extend({ toMatchImageSnapshot });

require("dotenv").config();
const options = {
    showPrefix: false,
    showMatcherMessage: true,
    showStack: true,
};

let browser;
let page;

const user = {
    name: "John Doe",
    username: "johndoe",
    email: "johndoe@gmail.com",
    password: "12345678",
};

beforeAll(async () => {
    mongoose.set("strictQuery", true);
    await mongoose.connect(process.env.MONGODB_URI, {
        useNewUrlParser: true,
        useUnifiedTopology: true,
    });
    await mongoose.connection
        .collection("users")
        .findOneAndDelete({ username: user.username });

    user.password = bcrypt.hashSync(user.password, bcrypt.genSaltSync(10));
    await mongoose.connection.collection("users").insertOne(user);
    user.password = "12345678";

    browser = await puppeteer.launch({
        headless: true,
        slowMo: 0,
        devtools: false,
        defaultViewport: {
            width: 1024,
            height: 768,
        },
    });
    page = await browser.newPage();
    await page.setDefaultTimeout(10000);
    await page.setDefaultNavigationTimeout(20000);
});

beforeEach(async () => {
    await page.goto(`http://localhost:${process.env.PORT}/login`);
});

afterAll(async () => {
    await mongoose.connection
        .collection("users")
        .findOneAndDelete({ username: user.username });
    await mongoose.connection.close();
    await browser.close();
});

describe("Testing the login page", () => {
    it("should have the right title", async () => {
        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "Auth-Experiment | Login" Make sure that the function handling the GET "/login" route is sending the right title`,
            options
        ).toBe("Auth-Experiment | Login");
    });

    it("should have a form with 2 inputs and 1 button", async () => {
        const username_label = await page.$eval(
            "label[for='username']",
            (el) => ({
                innerText: el.innerText,
            })
        );
        expect(
            username_label,
            `The form should contain a label with the text "Username or Email" and the attribute "for" with the value "username" Check the "login.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ innerText: "Username or Email" });
        const username_input = await page.$eval("#username", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            username_input,
            `The form should contain an input with the name "username" and the type "text" Check the "login.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "username", type: "text" });
        const password_input = await page.$eval("#password", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            password_input,
            `The form should contain an input with the name "password" and the type "password" Check the "login.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "password", type: "password" });

        const button = await page.$eval("button", (el) => ({
            type: el.type,
            text: el.textContent,
        }));
        expect(
            button,
            `The form should contain a button with the text "Login" and the type "submit" Check the "login.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ type: "submit", text: "Login" });
    });

    it("should login a user", async () => {
        await page.type("#username", user.username);
        await page.type("#password", user.password);
        await page.click("button");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const url = await page.url();
        expect(
            url,
            `The user should be redirected to the profile page after logging in, the current url is "${url}"`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/profile`);
    });

    it("should have the name of the user in the index page", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/`);
        const message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `The message "${message}" is wrong it should be "Hello ${user.name}" Make sure that the function handling the GET "/profile" route is sending the right message`,
            options
        ).toBe(`Hello ${user.name}`);
    });
});

describe("Testing the login page image snapshots", () => {
    it("matches the expected styling for the login page", async () => {
        if (!fs.existsSync("tests/web/images/login-page.png")) {
            throw new Error(
                `The reference image for the login page does not exist, please import the image from the "tests/web/images/login-page.png"`
            );
        }
        const client = await page.target().createCDPSession();
        await client.send("Network.clearBrowserCookies");
        await client.send("Network.clearBrowserCache");
        await page.goto(`http://localhost:${process.env.PORT}/login`);
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the login page is not correct check the file "tests/web/images/__diff_output__/login-page-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "login-page",
        });
    });

    it("matches the expected styling for the login page with error", async () => {
        if (!fs.existsSync("tests/web/images/login-page-with-error.png")) {
            throw new Error(
                `The reference image for the login page with error does not exist, please import the image from the "tests/web/images/login-page-with-error.png"`
            );
        }
        await page.type("#username", user.username);
        await page.type("#password", "wrongpassword");
        await page.click("button");
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the login page with error is not correct check the file "tests/web/images/__diff_output__/login-page-with-error-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "login-page-with-error",
        });
    });
});
