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
});

afterAll(async () => {
    await browser.close();
});

describe("Testing the delete form in the details page", () => {
    it("should delete a product", async () => {
        await page.click("form > button.btn");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        const url = await page.url();
        expect(
            url,
            `The url for deleting "${url}" a product is not correct, it should be "http://localhost:${process.env.PORT}/products/delete/${product.slug}"`,
            options
        ).toBe(
            `http://localhost:${process.env.PORT}/products/delete/${product.slug}`
        );

        const message = await page.$eval("p.message", (el) => el.textContent);
        expect(
            message,
            `The message for deleting "${message}" a product is not correct, it should be "Product deleted"`,
            options
        ).toBe("Product deleted");

        await page.goto(`http://localhost:${process.env.PORT}/products`);

        const products_data = await page.$$eval("tbody tr", (rows) =>
            rows.map((row) => {
                const [no, name, price, description, slug] = row.children;
                return {
                    name: name.textContent,
                    price: parseFloat(price.textContent.replace("$", "")),
                    description: description.textContent,
                    slug: slug.children[0].href.split("/show/").pop(),
                };
            })
        );
        initial_data.forEach((product) => {
            delete product._id;
            delete product.createdAt;
            delete product.updatedAt;
        });

        products_data.sort((a, b) => a.name.localeCompare(b.name));
        initial_data.sort((a, b) => a.name.localeCompare(b.name));

        expect(
            products_data,
            `The deleted product should not be in the list of products`,
            options
        ).not.toContainEqual(product);
    });

    it("should don't delete the product if the product does not exist", async () => {
        await page.setRequestInterception(true);
        page.on("request", (interceptedRequest) => {
            var data = {
                method: "POST",
            };
            interceptedRequest.continue(data);
        });

        await page.goto(
            `http://localhost:${process.env.PORT}/products/delete/1234567890`
        );

        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "API-Experiment | Error" Make sure that the function handling the POST deleteProduct method return the error title if the product was not found`,
            options
        ).toBe("API-Experiment | Error");
        const statusCode = await page.$eval(".title", (el) => el.textContent);
        expect(
            statusCode,
            `The status code "${statusCode}" is wrong it should be "404" Make sure that the function handling the POST deleteProduct method return the error status code if the product was not found`,
            options
        ).toBe("404");
        const message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `The message "${message}" is wrong it should be "No product found" Make sure that the function handling the POST deleteProduct method return the error message if the product was not found`,
            options
        ).toBe("No product found");
    });
});
