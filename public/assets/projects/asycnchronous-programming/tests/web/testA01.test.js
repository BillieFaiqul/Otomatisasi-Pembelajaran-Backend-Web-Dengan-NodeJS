const fs = require("fs");
const puppeteer = require("puppeteer");
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

beforeAll(async () => {
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
    await browser.close();
});

describe("Testing the index page title and content", () => {
    it("should have the right title", async () => {
        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "API-Experiment | Home" Make sure that the function handling the GET "/" route is sending the right title`,
            options
        ).toBe("API-Experiment | Home");
    });

    it("should have a button with the text 'Products' and url `/products` ", async () => {
        const button = await page.$eval(
            ".btn.btn-primary",
            (el) => el.textContent
        );
        expect(
            button,
            `The button with the text "Products" is not present on the page`,
            options
        ).toBe("Products");

        const url = await page.$eval(".btn.btn-primary", (el) => el.href);
        expect(
            url,
            `The button with the text "Products" is not sending the user to the right url`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/products`);

        const backgroundColor = await page.evaluate(() => {
            const button = document.querySelector(".btn.btn-primary");
            const style = window.getComputedStyle(button);
            return style.getPropertyValue("background-color");
        });
        expect(
            backgroundColor,
            `The button has the wrong background color "${backgroundColor}" it should be "rgb(0, 161, 189)"`
        ).toBe("rgb(0, 161, 189)");
    });

    it("should have nav bar with 2 links", async () => {
        const navBar = await page.$eval("nav", (el) => el.textContent);
        expect(
            navBar,
            `The page should contain a link to the home page. Check the "main.ejs" file in the "web/views/layouts" folder to find the nav bar"`,
            options
        ).toContain("Home");
        expect(
            navBar,
            `The page should contain a link to the products page. Check the "main.ejs" file in the "web/views/layouts" folder to find the nav bar`,
            options
        ).toContain("Products");
    });
});

describe("Testing the index page for receiving messages", () => {
    it("should receive a message and display it", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/?message=Hello test`
        );
        let message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `the message "${message}" received is wrong it should be "Hello test"`,
            options
        ).toBe("Hello test");

        await page.goto(
            `http://localhost:${process.env.PORT}/?message=This is another test`
        );
        message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `the message "${message}" received is wrong it should be "This is another test"`,
            options
        ).toBe("This is another test");
    });

    it("should have the correct color for the box after receiving a message", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/?message=yet, another test`
        );
        const backgroundColor = await page.evaluate(() => {
            const message = document.querySelector(".alert.alert-success");
            const style = window.getComputedStyle(message);
            return style.getPropertyValue("background-color");
        });
        expect(
            backgroundColor,
            `The message box has the wrong background color "${backgroundColor}" it should be "rgb(239, 162, 95)"`
        ).toBe("rgb(239, 162, 95)");
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
            `The title for the web page "${title}" is wrong it should be "API-Experiment | Error" Make sure that the function handling the GET "/:url" route is sending the right title`,
            options
        ).toBe("API-Experiment | Error");
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

describe("Testing the index page and error `Not Found` page image snapshots", () => {
    it("matches the expected styling", async () => {
        if (!fs.existsSync("tests/web/images/index-page.png")) {
            throw new Error(
                `The reference image for the index page does not exist, please import the image from the "tests/web/images/index-page.png"`
            );
        }
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

    it("matches the expected styling", async () => {
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
