const fs = require("fs");
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
    await page.goto(`http://localhost:${process.env.PORT}/`);
});

afterAll(async () => {
    await mongoose.connection
        .collection("users")
        .findOneAndDelete({ username: user.username });
    await mongoose.connection.close();
    await browser.close();
});

describe("Testing the index page title and content", () => {
    it("should have the right title", async () => {
        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "Auth-Experiment | Home" Make sure that the function handling the GET "/" route is sending the right title`,
            options
        ).toBe("Auth-Experiment | Home");
    });

    it("should have nav bar with 3 links", async () => {
        const navBar = await page.$eval("nav", (el) => el.textContent);
        expect(
            navBar,
            `The page should contain a link to the home page. Check the "main.ejs" file in the "web/views/layouts" folder to find the nav bar"`,
            options
        ).toContain("Home");
        expect(
            navBar,
            `The page should contain a link to the register page. Check the "main.ejs" file in the "web/views/layouts" folder to find the nav bar`,
            options
        ).toContain("Register");
        expect(
            navBar,
            `The page should contain a link to the login page. Check the "main.ejs" file in the "web/views/layouts" folder to find the nav bar`,
            options
        ).toContain("Login");
    });
});

describe("Testing the error `Not Found` page", () => {
    it("should have the right title", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/thisurldoesnotexist`
        );
        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "Auth-Experiment | Error" Make sure that the function handling the GET "/:url" route is sending the right title`,
            options
        ).toBe("Auth-Experiment | Error");
    });

    it("should have a status code of 404", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/thisurldoesnotexist`
        );
        const statusCode = await page.$eval(".title", (el) => el.textContent);
        expect(
            statusCode,
            `The status code "${statusCode}" is wrong it should be "404" Make sure that the function handling the GET "/:url" route is sending the right status code`,
            options
        ).toBe("404");
    });

    it("should have a message saying `NOT FOUND`", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/thisurldoesnotexist`
        );
        const message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `The message "${message}" is wrong it should be "NOT FOUND" Make sure that the function handling the GET "/:url" route is sending the right message`,
            options
        ).toBe("NOT FOUND");
    });
});

describe("Testing the register page", () => {
    it("should have the right title", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/register`);
        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "Auth-Experiment | Register" Make sure that the function handling the GET "/register" route is sending the right title`,
            options
        ).toBe("Auth-Experiment | Register");
    });

    it("should have a form with 5 inputs and 1 button", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/register`);
        const name_input = await page.$eval("#name", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            name_input,
            `The form should contain an input with the name "name" and the type "text" Check the "register.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "name", type: "text" });
        const username_input = await page.$eval("#username", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            username_input,
            `The form should contain an input with the name "username" and the type "text" Check the "register.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "username", type: "text" });
        const email_input = await page.$eval("#email", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            email_input,
            `The form should contain an input with the name "email" and the type "email" Check the "register.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "email", type: "email" });
        const password_input = await page.$eval("#password", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            password_input,
            `The form should contain an input with the name "password" and the type "password" Check the "register.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "password", type: "password" });
        const confirmPassword_input = await page.$eval(
            "#confirmPassword",
            (el) => ({
                name: el.name,
                type: el.type,
            })
        );
        expect(
            confirmPassword_input,
            `The form should contain an input with the name "confirmPassword" and the type "password" Check the "register.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ name: "confirmPassword", type: "password" });

        const button = await page.$eval("button", (el) => ({
            type: el.type,
            text: el.textContent,
        }));
        expect(
            button,
            `The form should contain a button with the text "Register" and the type "submit" Check the "register.ejs" file in the "web/views/auth" folder to find the form`,
            options
        ).toEqual({ type: "submit", text: "Register" });
    });

    it("should register a new user", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/register`);
        await page.type("#name", user.name);
        await page.type("#username", user.username);
        await page.type("#email", user.email);
        await page.type("#password", user.password);
        await page.type("#confirmPassword", user.password);
        await page.click("button");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const url = await page.url();
        expect(
            url,
            `The user should be redirected to the profile page after registering, the current url is "${url}"`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/profile`);
    });

    it("should have the name of the user in the index page", async () => {
        const message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `The message "${message}" is wrong it should be "Hello ${user.name}" Make sure that the function handling the GET "/profile" route is sending the right message`,
            options
        ).toBe(`Hello ${user.name}`);
    });
});

describe("Testing the index page, register page, and error `Not Found` page image snapshots", () => {
    it("matches the expected styling for the index page after register", async () => {
        if (!fs.existsSync("tests/web/images/index-page-after-register.png")) {
            throw new Error(
                `The reference image for the index after register page does not exist, please import the image from the "tests/web/images/index-page-after-register.png"`
            );
        }
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the index page is not correct check the file "tests/web/images/__diff_output__/index-page-after-register-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "index-page-after-register",
        });
    });

    it("matches the expected styling for the index page before register", async () => {
        // restart the browser and go to the index page
        const client = await page.target().createCDPSession();
        await client.send("Network.clearBrowserCookies");
        await client.send("Network.clearBrowserCache");
        if (!fs.existsSync("tests/web/images/index-page.png")) {
            throw new Error(
                `The reference image for the index page does not exist, please import the image from the "tests/web/images/index-page.png"`
            );
        }
        await page.goto(`http://localhost:${process.env.PORT}/`);
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the index page is not correct check the file "tests/web/images/__diff_output__/index-page-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "index-page",
        });
    });

    it("matches the expected styling for the register page", async () => {
        if (!fs.existsSync("tests/web/images/register-page.png")) {
            throw new Error(
                `The reference image for the register page does not exist, please import the image from the "tests/web/images/register-page.png"`
            );
        }
        await page.goto(`http://localhost:${process.env.PORT}/register`);
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the register page is not correct check the file "tests/web/images/__diff_output__/register-page-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "register-page",
        });
    });

    it("matches the expected styling for the error page", async () => {
        if (!fs.existsSync("tests/web/images/error-notFound-page.png")) {
            throw new Error(
                `The reference image for the error page does not exist, please import the image from the "tests/web/images/error-notFound-page.png"`
            );
        }
        await page.goto(
            `http://localhost:${process.env.PORT}/thisurldoesnotexist`
        );
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the error "Not Found" page is not correct check the file "tests/web/images/__diff_output__/error-notFound-page-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "error-notFound-page",
        });
    });
});
