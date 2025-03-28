const fs = require("fs");
const puppeteer = require("puppeteer");
const { toMatchImageSnapshot } = require("jest-image-snapshot");
const initial_data = JSON.parse(fs.readFileSync("./initial_data.json"));
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
    await page.goto(`http://localhost:${process.env.PORT}/products`);
});

afterAll(async () => {
    await browser.close();
});

describe("Testing the products page title and content", () => {
    it("should have the right title", async () => {
        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "API-Experiment | Products" Make sure that the function handling the GET "/products" route is sending the right title`,
            options
        ).toBe("API-Experiment | Products");
    });

    it("should have a header with the text 'Products' with 'title' class", async () => {
        const header = await page.$eval(".title", (el) => el.textContent);
        expect(
            header,
            `The header with the text "Products" is not present on the page`,
            options
        ).toBe("Products");
    });

    it("should have the search form with the three inputs and the submit button", async () => {
        const inputs = await page.$$("input");
        expect(
            inputs.length,
            `The number of inputs in the search form is wrong, it should be 3`,
            options
        ).toBe(3);

        const button = await page.$eval(
            "form > button.btn.btn-primary",
            (el) => el.textContent
        );
        expect(
            button,
            `The submit button is not present on the page`,
            options
        ).toBeTruthy();
        expect(
            button,
            `The submit button should have the text "Search", but it has "${button}"`,
            options
        ).toBe("Search");
    });

    it("should have the correct form action and method", async () => {
        const form = await page.$eval("form", (el) => ({
            action: el.action,
            method: el.method,
        }));
        expect(
            form.action,
            `The form action should be "http://localhost:${process.env.PORT}/products", but it has "${form.action}", You can change it in the "web/views/products/index.ejs" file.`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/products`);
        expect(
            form.method,
            `The form method should be "GET", but it has "${form.method}", You can change it in the "web/views/products/index.ejs" file.`,
            options
        ).toBe("get");
    });

    it("should the right inputs names and types", async () => {
        const search_input = await page.$eval("#search", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            search_input.name,
            `The first input should have the name "search", but it has "${search_input.name}"`,
            options
        ).toBe("search");
        expect(
            search_input.type,
            `The first input should have the type "text", but it has "${search_input.type}"`,
            options
        ).toBe("text");

        const minPrice_input = await page.$eval("#minPrice", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            minPrice_input.name,
            `The second input should have the name "price[minPrice]", but it has "${minPrice_input.name}"`,
            options
        ).toBe("price[minPrice]");
        expect(
            minPrice_input.type,
            `The second input should have the type "number", but it has "${minPrice_input.type}"`,
            options
        ).toBe("number");

        const maxPrice_input = await page.$eval("#maxPrice", (el) => ({
            name: el.name,
            type: el.type,
        }));
        expect(
            maxPrice_input.name,
            `The third input should have the name "price[maxPrice]", but it has "${maxPrice_input.name}"`,
            options
        ).toBe("price[maxPrice]");
        expect(
            maxPrice_input.type,
            `The third input should have the type "number", but it has "${maxPrice_input.type}"`,
            options
        ).toBe("number");
    });

    it("should have a button to create a new product", async () => {
        const button = await page.$eval(".btn.btn-primary", (el) => ({
            text: el.textContent,
            url: el.href,
        }));
        expect(
            button.text,
            `The button to create a new product should have the text "Create a new product", but it has "${button.text}"`,
            options
        ).toBe("Create a new product");
        expect(
            button.url,
            `The button to create a new product should have the url "http://localhost:${process.env.PORT}/products/create", but it has "${button.url}"`,
            options
        ).toBe(`http://localhost:${process.env.PORT}/products/create`);
    });
});

describe("Testing the products page table", () => {
    it("should have the right number of products", async () => {
        const products = await page.$$("tbody tr");
        expect(
            products.length,
            `The number of products is wrong, it should be 10`,
            options
        ).toBe(10);
    });

    it("should have the right data", async () => {
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

        expect(products_data, `The products data is wrong`, options).toEqual(
            initial_data
        );
    });
});

describe("Testing the products details page", () => {
    it("should go to the details page when clicking on a product", async () => {
        await page.click("tbody tr:first-child a");
        const url = await page.url();
        const slug = url.split("/show/").pop();
        const product = initial_data.find((product) => product.slug === slug);
        expect(
            product,
            `The product with the slug "${slug}" is not present in the initial_data.json file`,
            options
        ).toBeTruthy();
        expect(
            url,
            `The url for the details page is wrong, it should be "http://localhost:${process.env.PORT}/products/show/${product.slug}", but it is "${url}"`,
            options
        ).toBe(
            `http://localhost:${process.env.PORT}/products/show/${product.slug}`
        );
    });

    it("should have the button to edit and delete the product", async () => {
        await page.click("tbody tr:first-child a");
        const url = await page.url();
        const slug = url.split("/show/").pop();
        const product = initial_data.find((product) => product.slug === slug);
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
        const editButton = await page.$eval("a.btn", (el) => ({
            text: el.textContent.trim(),
            url: el.href.trim(),
        }));
        const deleteButton = await page.$eval("form > button.btn", (el) => ({
            text: el.textContent.trim(),
            url: el.parentElement.action.trim(),
        }));

        expect(
            productName,
            `The product name is wrong, it should be "${product.name}", but it is "${productName}"`,
            options
        ).toBe(product.name);
        expect(
            productPrice,
            `The product price is wrong, it should be "${product.price}", but it is "$${productPrice}"`,
            options
        ).toBe("$" + product.price);
        expect(
            productDescription,
            `The product description is wrong, it should be "${product.description}", but it is "${productDescription}"`,
            options
        ).toBe(product.description);
        expect(
            editButton.text,
            `The edit button should have the text "Edit this product", but it has "${editButton.text}", change it in the "views/products/details.ejs" file`,
            options
        ).toBe("Edit this product");
        expect(
            editButton.url,
            `The edit button should have the url "http://localhost:${process.env.PORT}/products/update/${product.slug}", but it has "${editButton.url}", make sure you are using the correct slug by using "/products/update/<%= product.slug %>" url, change it in the "views/products/details.ejs" file`,
            options
        ).toBe(
            `http://localhost:${process.env.PORT}/products/update/${product.slug}`
        );
        expect(
            deleteButton.text,
            `The delete button should have the text "Delete this product", but it has "${deleteButton.text}", change it in the "views/products/details.ejs" file`,
            options
        ).toBe("Delete this product");
        expect(
            deleteButton.url,
            `The delete button should have the url "http://localhost:${process.env.PORT}/products/delete/${product.slug}", but it has "${deleteButton.url}", make sure you are using the correct slug by using "/products/delete/<%= product.slug %>" url, change it in the "views/products/details.ejs" file`,
            options
        ).toBe(
            `http://localhost:${process.env.PORT}/products/delete/${product.slug}`
        );
    });

    it("should don't go to the product's details page if the product does not exist", async () => {
        await page.goto(
            `http://localhost:${process.env.PORT}/products/show/123thisproductdoenotexist`
        );

        const title = await page.title();
        expect(
            title,
            `The title for the web page "${title}" is wrong it should be "API-Experiment | Error" Make sure that the function handling the GET getProduct method return the error title if the product was not found`,
            options
        ).toBe("API-Experiment | Error");
        const statusCode = await page.$eval(".title", (el) => el.textContent);
        expect(
            statusCode,
            `The status code "${statusCode}" is wrong it should be "404" Make sure that the function handling the GET getProduct method return the error status code if the product was not found`,
            options
        ).toBe("404");
        const message = await page.$eval(".message", (el) => el.textContent);
        expect(
            message,
            `The message "${message}" is wrong it should be "No product found" Make sure that the function handling the GET getProduct method return the error message if the product was not found`,
            options
        ).toBe("No product found");
    });
});

describe("Testing the product pages image snapshots", () => {
    it("should have the right image for the products page", async () => {
        if (!fs.existsSync("tests/web/images/products-table-page.png")) {
            throw new Error(
                `The reference image for the products table page does not exist, please import the image from the "tests/web/images/products-table-page.png"`
            );
        }

        const image = await page.screenshot({ fullPage: true });
        expect(
            image,
            `The image for the products table page is wrong, it should be the same as the "tests/web/images/__diff_output__/products-table-page-diff.png" image`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "products-table-page",
        });
    });

    it("should have the right image for the details page", async () => {
        if (!fs.existsSync("tests/web/images/product-details-page.png")) {
            throw new Error(
                `The reference image for the product details page does not exist, please import the image from the "tests/web/images/product-details-page.png"`
            );
        }

        await page.goto(
            `http://localhost:${process.env.PORT}/products/show/${initial_data[0].slug}`
        );
        const image = await page.screenshot({ fullPage: true });
        expect(
            image,
            `The image for the product details page is wrong, it should be the same as the "tests/web/images/__diff_output__/product-details-page-diff.png" image`,
            options
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "product-details-page",
        });
    });

    it("should match the not found product snapshot", async () => {
        if (!fs.existsSync("tests/web/images/not-found-product-page.png")) {
            throw new Error(
                `The reference image for the not found product page does not exist, please import the image from the "tests/web/images/not-found-product-page.png"`
            );
        }

        await page.goto(
            `http://localhost:${process.env.PORT}/products/show/123`
        );
        const image = await page.screenshot({ fullPage: true });
        expect(
            image,
            `The image for the not found product page is wrong, it should be the same as the "tests/web/images/__diff_output__/not-found-product-page-diff.png" image`
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "not-found-product-page",
        });
    });

    it("should match no products found snapshot", async () => {
        if (!fs.existsSync("tests/web/images/no-products-found-page.png")) {
            throw new Error(
                `The reference image for the no products found page does not exist, please import the image from the "tests/web/images/no-products-found-page.png"`
            );
        }

        await page.goto(
            `http://localhost:${process.env.PORT}/products?search=123ThisIsNotAProduct`
        );
        const image = await page.screenshot({ fullPage: true });
        expect(
            image,
            `The image for the no products found page is wrong, it should be the same as the "tests/web/images/__diff_output__/no-products-found-page-diff.png" image`
        ).toMatchImageSnapshot({
            customDiffConfig: { threshold: 0.9 },
            customSnapshotsDir: "tests/web/images",
            customSnapshotIdentifier: "no-products-found-page",
        });
    });
});
