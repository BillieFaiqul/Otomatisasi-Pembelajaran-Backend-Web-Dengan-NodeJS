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
let product;

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

    await page.goto(`http://localhost:${process.env.PORT}/products`);
    await page.click("tbody tr:first-child a");
    const url = await page.url();
    const slug = url.split("/show/").pop();
    product = initial_data.find((product) => product.slug === slug);

    await page.goto(
        `http://localhost:${process.env.PORT}/products/update/${slug}`
    );
});

afterAll(async () => {
    await browser.close();
});

describe("Testing the update page title and content", () => {
    it("should have the correct title", async () => {
        const title = await page.title();
        expect(
            title,
            `The title received "${title}" of the page is not correct, it should be "API-Experiment | Update Product". Change the title of the page to match the expected one. You can change it in the "controllers/web/product.controller.js" file.`,
            options
        ).toBe("API-Experiment | Update Product");
    });
    it("should have the correct content title and description", async () => {
        const title = await page.$eval(".title", (el) => el.textContent);
        const description = await page.$eval(
            ".description",
            (el) => el.textContent
        );

        expect(
            title,
            `The title received "${title}" of the page's header is not correct, it should be "Update this product". Change the title of the page to match the expected one. You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("Update this product");

        expect(
            description,
            `The description received "${description}" of the page's header is not correct, it should be "Fill the form below to update this product". Change the description of the page to match the expected one. You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("Fill the form below to update this product");
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
            `The submit button is not present on the page, You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBeTruthy();
        expect(
            button,
            `The submit button should have the text "Update", but it has "${button}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("Update");
    });

    it("should the right inputs names and types", async () => {
        const name_input = await page.$eval("#name", (el) => ({
            name: el.name,
            type: el.type,
            value: el.value,
        }));
        expect(
            name_input.name,
            `The name input should have the name "name", but it has "${name_input.name}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("name");
        expect(
            name_input.type,
            `The name input should have the type "text", but it has "${name_input.type}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("text");
        expect(
            name_input.value,
            `The name input should have the value "${product.name}", but it has "${name_input.value}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe(product.name);

        const price_input = await page.$eval("#price", (el) => ({
            name: el.name,
            type: el.type,
            value: el.value,
        }));
        expect(
            price_input.name,
            `The price input should have the name "price", but it has "${price_input.name}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("price");
        expect(
            price_input.type,
            `The price input should have the type "number", but it has "${price_input.type}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("number");
        expect(
            price_input.value,
            `The price input should have the value "${product.price}", but it has "${price_input.value}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe(product.price.toString());

        const description_input = await page.$eval("#description", (el) => ({
            name: el.name,
            rows: el.rows,
            value: el.textContent.trim(),
        }));
        expect(
            description_input.name,
            `The description textarea should have the name "description", but it has "${description_input.name}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("description");
        expect(
            description_input.rows,
            `The description textarea should have "5" rows "number", but it has "${description_input.rows}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe(5);
        expect(
            description_input.value,
            `The description textarea should have the value "${product.description}", but it has "${description_input.value}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe(product.description);
    });

    it("should have the correct form action and method", async () => {
        const form = await page.$eval("form", (el) => ({
            action: el.action,
            method: el.method,
        }));
        expect(
            form.action,
            `The form action should be "http://localhost:${process.env.PORT}/products/update/${product.slug}", but it has "${form.action}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe(
            `http://localhost:${process.env.PORT}/products/update/${product.slug}`
        );
        expect(
            form.method,
            `The form method should be "POST", but it has "${form.method}", You can change it in the "web/views/products/update.ejs" file.`,
            options
        ).toBe("post");
    });
});

describe("Testing the create product page form submission", () => {
    it("should update the product", async () => {
        let nameInput = await page.$("#name");
        let priceInput = await page.$("#price");
        let descriptionInput = await page.$("#description");
        await nameInput.click({ clickCount: 3 });
        await nameInput.press("Backspace");
        await priceInput.click({ clickCount: 3 });
        await priceInput.press("Backspace");
        await descriptionInput.click({ clickCount: 3 });
        await descriptionInput.press("Backspace");
        await nameInput.type("Updated product");
        await priceInput.type("99.99");
        await descriptionInput.type("Updated description");
        await page.click("form > button.btn.btn-primary");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const url = await page.url();
        expect(
            url,
            `The page url should be "http://localhost:${process.env.PORT}/products/update/${product.slug}", but it has "${url}", You can change it in the "controllers/web/products.controller.js" file.`,
            options
        ).toBe(
            `http://localhost:${process.env.PORT}/products/update/${product.slug}`
        );
        const message = await page.$eval("p.message", (el) => el.textContent);
        expect(
            message,
            `The message should be "Product updated", but it has "${message}", You can change it in the "controllers/web/products.controller.js" file.`,
            options
        ).toBe("Product updated");

        const productName = await page.$eval(
            ".card-title",
            (el) => el.textContent
        );
        const productPrice = await page.$eval(
            ".card-subtitle",
            (el) => el.textContent
        );
        const productDescription = await page.$eval(
            ".card-text",
            (el) => el.textContent
        );

        expect(
            productName,
            `The product after updated should have the new updated name, but it has "${productName}", You can change the update method in the "controllers/web/products.controller.js" file.`,
            options
        ).toBe("Updated product");
        expect(
            productPrice,
            `The product after updated should have the new updated price, but it has "${productPrice}", You can change the update method in the "controllers/web/products.controller.js" file.`,
            options
        ).toBe("$99");
        expect(
            productDescription,
            `The product after updated should have the new updated description, but it has "${productDescription}", You can change the update method in the "controllers/web/products.controller.js" file.`,
            options
        ).toBe("Updated description");
    });

    it("should not update the product if the name is empty", async () => {
        let nameInput = await page.$("#name");
        await nameInput.click({ clickCount: 3 });
        await nameInput.press("Backspace");
        await page.click("form > button.btn.btn-primary");
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const message = await page.$eval("p.message", (el) => el.textContent);
        expect(
            message,
            `The message received "${message}" of the page is not correct, it should be "Please fill all fields". Change the message of the page to match the expected one. You can change it in the "controllers/web/product.controller.js" file.`,
            options
        ).toBe("Please fill all fields");
    });

    it("should don't update the product if the product does not exist", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/products/update/123thisproductdoenotexist`
        );

        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "API-Experiment | Error" Make sure that the function handling the GET updateProduct method return the error title if the product was not found`,
            options
        ).toBe("API-Experiment | Error");
        const statusCode = await page.$eval(".title", (el) => el.textContent);
        expect(
            statusCode,
            `The status code "${statusCode}" is wrong it should be "404" Make sure that the function handling the GET updateProduct method return the error status code if the product was not found`,
            options
        ).toBe("404");
        const message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `The message "${message}" is wrong it should be "No product found" Make sure that the function handling the GET updateProduct method return the error message if the product was not found`,
            options
        ).toBe("No product found");
    });
});

describe("Testing the update product page image snapshot", () => {
    it("should match the update product page image snapshot", async () => {
        let nameInput = await page.$("#name");
        let priceInput = await page.$("#price");
        let descriptionInput = await page.$("#description");
        await nameInput.click({ clickCount: 3 });
        await nameInput.press("Backspace");
        await priceInput.click({ clickCount: 3 });
        await priceInput.press("Backspace");
        await descriptionInput.click({ clickCount: 3 });
        await descriptionInput.press("Backspace");

        if (!fs.existsSync("tests/web/images/update-product-page.png")) {
            throw new Error(
                `The reference image for the update product page does not exist, please import the image from the "tests/web/images/update-product-page.png"`
            );
        }

        const image = await page.screenshot({ fullPage: true });
        expect(
            image,
            `The image for the update product page is wrong, it should be the same as the "tests/web/images/__diff_output__/update-product-page-diff.png" image`
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "update-product-page",
        });
    });
});
