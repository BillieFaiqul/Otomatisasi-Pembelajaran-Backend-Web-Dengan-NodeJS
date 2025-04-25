const fs = require("fs");
const puppeteer = require("puppeteer");
const { toMatchImageSnapshot } = require("jest-image-snapshot");
const initial_data = JSON.parse(fs.readFileSync("./initial_data.json"));
const mongoose = require("mongoose");
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
    await page.goto(`http://localhost:${process.env.PORT}/products/create`);
    mongoose.set("strictQuery", true);
    await mongoose.connect(process.env.MONGODB_URI, {
        useNewUrlParser: true,
        useUnifiedTopology: true,
    });
    await mongoose.connection.collection("products").deleteMany({});
    initial_data.forEach((product) => {
        delete product._id;
        delete product.createdAt;
        delete product.updatedAt;
    });
    await mongoose.connection.collection("products").insertMany(initial_data);
    await mongoose.connection.close();
});

afterAll(async () => {
    await browser.close();
});

describe("Testing the create product page title and content", () => {
    it("should have the correct title", async () => {
        const title = await page.title();
        expect(
            title,
            `The title received "${title}" of the page is not correct, it should be "API-Experiment | Create Product". Change the title of the page to match the expected one. You can change it in the "controllers/web/product.controller.js" file.`,
            options
        ).toBe("API-Experiment | Create Product");
    });

    it("should have the correct content title and description", async () => {
        const title = await page.$eval(".title", (el) => el.textContent);
        const description = await page.$eval(
            ".description",
            (el) => el.textContent
        );

        expect(
            title,
            `The title received "${title}" of the page's header is not correct, it should be "Create a new product". Change the title of the page to match the expected one. You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("Create a new product");

        expect(
            description,
            `The description received "${description}" of the page's header is not correct, it should be "Fill the form below to create a new product". Change the description of the page to match the expected one. You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("Fill the form below to create a new product");
    });
});

describe("Testing the create product page form", () => {
    it("should have the correct form fields", async () => {
        const inputs = await page.$$("input");
        expect(
            inputs.length,
            `The number of inputs in the create form is wrong, it should be 2`,
            options
        ).toBe(2);

        const textarea = await page.$$("textarea");
        expect(
            textarea.length,
            `The number of textarea in the create form is wrong, it should be 1`,
            options
        ).toBe(1);

        const button = await page.$eval(
            "form > button.btn.btn-primary",
            (el) => el.textContent
        );
        expect(
            button,
            `The submit button is not present on the page, You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBeTruthy();
        expect(
            button,
            `The submit button should have the text "Create", but it has "${button}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("Create");
    });

    it("should the right inputs names and types", async () => {
        const name_input = await page.$eval("#name", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            name_input.name,
            `The name input should have the name "name", but it has "${name_input.name}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("name");
        expect(
            name_input.type,
            `The name input should have the type "text", but it has "${name_input.type}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("text");

        const price_input = await page.$eval("#price", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            price_input.name,
            `The price input should have the name "price", but it has "${price_input.name}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("price");
        expect(
            price_input.type,
            `The price input should have the type "number", but it has "${price_input.type}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("number");

        const description_input = await page.$eval("#description", (el) => ({
            name: el.name,
            rows: el.rows,
        }));
        expect(
            description_input.name,
            `The description textarea should have the name "description", but it has "${description_input.name}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("description");
        expect(
            description_input.rows,
            `The description textarea should have "5" rows "number", but it has "${description_input.rows}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe(5);
    });

    it("should have the correct form action and method", async () => {
        const form = await page.$eval("form", (el) => ({
            action: el.action,
            method: el.method,
        }));
        expect(
            form.action,
            `The form action should be "http://localhost:${process.env.PORT}/products/create", but it has "${form.action}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/products/create`);
        expect(
            form.method,
            `The form method should be "POST", but it has "${form.method}", You can change it in the "web/views/products/create.ejs" file.`,
            options
        ).toBe("post");
    });
});

describe("Testing the create product page form submission", () => {
    it("should create a new product", async () => {
        await page.type("#name", "Test Product");
        await page.type("#price", "100");
        await page.type("#description", "Test Product Description");
        await page.click("form > button.btn.btn-primary");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const message = await page.$eval("p.message", (el) => el.textContent);
        expect(
            message,
            `The message received "${message}" of the page is not correct, it should be "Product created". Change the message of the page to match the expected one. You can change it in the "controllers/web/product.controller.js" file.`,
            options
        ).toBe("Product created");

        const newProduct = await page.$eval(
            "table > tbody > tr:last-child",
            (el) => ({
                name: el.children[1].textContent,
                price: el.children[2].textContent,
                description: el.children[3].textContent,
            })
        );
        expect(
            newProduct,
            `The test product created seems to be not in the table of products, make sure that the product after being created is added to the table of products. You can change it in the "controllers/web/product.controller.js" file.`
        ).toEqual({
            name: "Test Product",
            price: "$100",
            description: "Test Product Description",
        });
    });

    it("should not create a new product with empty fields", async () => {
        await page.type("#name", "New Product");
        await page.click("form > button.btn.btn-primary");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const message = await page.$eval("p.message", (el) => el.textContent);
        expect(
            message,
            `The message received "${message}" of the page is not correct, it should be "Please fill all fields". Change the message of the page to match the expected one. You can change it in the "controllers/web/product.controller.js" file.`,
            options
        ).toBe("Please fill all fields");
    });
});

describe("Testing the create page image snapshot", () => {
    it("should match the reference image", async () => {
        if (!fs.existsSync("tests/web/images/create-product-page.png")) {
            throw new Error(
                `The reference image for the create product page does not exist, please import the image from the "tests/web/images/create-product-page.png"`
            );
        }
        const image = await page.screenshot({ fullPage: true });
        expect(
            image,
            `The image for the create product page is wrong, it should be the same as the "tests/web/images/__diff_output__/create-product-page-diff.png" image`
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "create-product-page",
        });
    });
});
