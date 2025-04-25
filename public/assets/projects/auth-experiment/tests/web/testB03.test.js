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

describe("Testing the profile page", () => {
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
    it("should have the name of the user in the profile page", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/profile`);
        const name = await page.$eval(".card-title", (el) => el.textContent);
        const username = await page.$eval(
            ".card-subtitle",
            (el) => el.textContent
        );
        const email = await page.$eval(".card-text", (el) => el.textContent);
        expect(
            name,
            `The name "${name}" is wrong it should be "Name: ${user.name}" Make sure that the function handling the GET "/profile" route is sending the right message, Make sure the profile.ejs page is using the right variable`,
            options
        ).toBe(`Name: ${user.name}`);
        expect(
            username,
            `The username "${username}" is wrong it should be "Username: ${user.username}" Make sure that the function handling the GET "/profile" route is sending the right message, Make sure the profile.ejs page is using the right variable`,
            options
        ).toBe(`Username: ${user.username}`);
        expect(
            email,
            `The email "${email}" is wrong it should be "Email: ${user.email}" Make sure that the function handling the GET "/profile" route is sending the right message, Make sure the profile.ejs page is using the right variable`,
            options
        ).toBe(`Email: ${user.email}`);
    });

    it("should have the right title in the profile page", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/profile`);
        const title = await page.$eval("title", (el) => el.textContent);
        expect(
            title,
            `The title "${title}" is wrong it should be "Auth-Experiment | Profile" Make sure that the function handling the GET "/profile" route is sending the right title`,
            options
        ).toBe(`Auth-Experiment | Profile`);
    });

    it("should have the right buttons and form in the profile page", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/profile`);

        const updateDataButton = await page.$eval(
            ".action > a:nth-child(1)",
            (el) => el.textContent
        );
        const updataPasswordButton = await page.$eval(
            ".action > a:nth-child(2)",
            (el) => el.textContent
        );
        const logoutButton = await page.$eval(
            ".action > a:nth-child(3)",
            (el) => el.textContent
        );
        const deleteButton = await page.$eval(
            ".action > form > button",
            (el) => el.textContent
        );
        const deleteForm = await page.$eval(".action > form", (el) =>
            el.getAttribute("action")
        );
        expect(
            updateDataButton,
            `The update data button "${updateDataButton}" is wrong it should be "Update Data" Make sure that the profile.ejs page is using the right names for the buttons`,
            options
        ).toBe(`Update Data`);
        expect(
            updataPasswordButton,
            `The update password button "${updataPasswordButton}" is wrong it should be "Update Password" Make sure that the profile.ejs page is using the right names for the buttons`,
            options
        ).toBe(`Update Password`);
        expect(
            logoutButton,
            `The logout button "${logoutButton}" is wrong it should be "Logout" Make sure that the profile.ejs page is using the right names for the buttons`,
            options
        ).toBe(`Logout`);
        expect(
            deleteButton,
            `The delete button "${deleteButton}" is wrong it should be "Delete" Make sure that the profile.ejs page is using the right names for the buttons`,
            options
        ).toBe(`Delete`);
        expect(
            deleteForm,
            `The delete form "${deleteForm}" is wrong it should be "/profile/delete" Make sure that the profile.ejs page is using the right names for the buttons`,
            options
        ).toBe(`/profile/delete`);
    });
});

describe("Testing the profile page image snapshots", () => {
    it("matches the expected styling for the profile page", async () => {
        if (!fs.existsSync("tests/web/images/profile-page.png")) {
            throw new Error(
                `The reference image for the profile page does not exist, please import the image from the "tests/web/images/profile-page.png"`
            );
        }
        await page.goto(`http://localhost:${process.env.PORT}/profile`);
        const screenshot = await page.screenshot({ fullPage: true });
        expect(
            screenshot,
            `The web styling for the profile page is not correct check the file "tests/web/images/__diff_output__/profile-page-diff.png" to find the difference`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "profile-page",
        });
    });
});
