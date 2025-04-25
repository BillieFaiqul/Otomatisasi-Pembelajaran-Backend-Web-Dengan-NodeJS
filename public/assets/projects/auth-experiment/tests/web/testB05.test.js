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

describe("Testing the logout button", () => {
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

    it("should logout users by clicking the logout button", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/profile`);
        await page.click(".action > a:nth-child(3)");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const url = await page.url();
        expect(
            url,
            `The user should be redirected to the home page after logging out, the current url is "${url}"`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/`);

        const cookies = await page.cookies();
        expect(
            cookies,
            `The user should be logged out, the cookies should be empty`,
            options
        ).toEqual([]);
    });
});

describe("Testing the delete button", () => {
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

    it("should delete the user's account", async () => {
        await page.goto(`http://localhost:${process.env.PORT}/profile`);
        await page.click(".action > form > button");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const url = await page.url();
        expect(
            url,
            `The user should be redirected to the home page after deleting the account, the current url is "${url}"`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/`);

        const cookies = await page.cookies();
        expect(
            cookies,
            `The user should be logged out, the cookies should be empty`,
            options
        ).toEqual([]);
    });

    it("should delete the user from the database", async () => {
        const userNull = await mongoose.connection
            .collection("users")
            .findOne({ username: user.username });
        expect(
            userNull,
            `The user should be deleted from the database`,
            options
        ).toBe(null);
    });

    it("should not login a deleted user", async () => {
        await page.type("#username", user.username);
        await page.type("#password", user.password);
        await page.click("button");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const url = await page.url();
        expect(
            url,
            `The user should not be able to login, the current url is "${url}"`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/login`);
    });
});
