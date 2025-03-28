const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const Product = require("../../models/product.model");

require("dotenv").config();
mongoose.set("strictQuery", true);

const options = {
  showPrefix: false,
  showMatcherMessage: true,
  showStack: true,
};

beforeAll(async () => {
  await connectDB(process.env.MONGODB_URI_TEST).then(
    async () => {
      console.log("Database connected successfully");
      await createProducts();
    },
    (err) => {
      console.log("There is problem while connecting database " + err);
    }
  );
});

describe("GET /api/v1/products", () => {
  it("should return all products", async () => {
    const res = await request(app).get("/api/v1/products");
    expect(
      res.statusCode,
      `When calling GET /api/v1/products, the status code should be "200", but it was "${res.statusCode}", update your code to return 200`,
      options
    ).toBe(200);
    expect(
      res.body.message,
      `When calling GET /api/v1/products, the message should be "Products found", but it was "${res.body.message}", update your code to return "Products found"`,
      options
    ).toBe("Products found");
    expect(
      res.body.products.length,
      `When calling GET /api/v1/products, the products array should return more than 0 products, but it was "${res.body.products.length}", update your code to return more than 0 products`,
      options
    ).toBeGreaterThan(0);
    expect(
      res.body.products[0].name === "Product 1" ||
        res.body.products[0].name === "Product 2",
      `The data received from GET /api/v1/products was not correct, update your code to return the correct data`,
      options
    ).toBeTruthy();
    expect(
      res.req.method,
      `When calling GET /api/v1/products, the method should be "GET", but it was "${res.req.method}", update the method to be "GET"`,
      options
    ).toBe("GET");
    expect(
      res.type,
      `When calling GET /api/v1/products, the content type should be "application/json", but it was "${res.type}", update the content type to be "application/json"`,
      options
    ).toBe("application/json");
  });

  it("should check items in database", async () => {
    await disconnectDB().then(async () => {
      await connectDB(process.env.MONGODB_URI).then(async () => {
        const products = await Product.find();
        expect(
          products.length,
          `The database should contain all the "10 products" from the initial_data.json file, but it was "${products.length}", use the initial_data.json file to add more products to the api-experiment database`,
          options
        ).toEqual(10);
        await disconnectDB().then(async () => {
          await connectDB(process.env.MONGODB_URI_TEST);
        });
      });
    });
  });

  it("should return no products", async () => {
    await Product.deleteMany();
    const res = await request(app).get("/api/v1/products");
    expect(
      res.statusCode,
      `The status code should be "404" because there are no products, but it was "${res.statusCode}", update the status code to be 404 when there are no products`,
      options
    ).toBe(404);
    expect(
      res.body.message,
      `The message should be "No products found" because there are no products, but it was "${res.body.message}", update the message to be "No products found" when there are no products`,
      options
    ).toBe("No products found");
    await createProducts();
  });

  it("should return error 500 if the database disconnected", async () => {
    await disconnectDB().then(async () => {
      const res = await request(app).get("/api/v1/products");
      expect(
        res.statusCode,
        `The application should return 500 for the status code if the database is disconnected`,
        options
      ).toBe(500);
      await connectDB(process.env.MONGODB_URI_TEST);
    });
  });
});

describe("GET /api/v1/product/:slug", () => {
  it("should return one product", async () => {
    const res = await request(app).get("/api/v1/product/product-2");
    expect(
      res.statusCode,
      `When calling GET /api/v1/product/:slug, the status code should be "200", but it was "${res.statusCode}", update your code to return 200`
    ).toBe(200);
    expect(
      res.body.product.name,
      `The expected product was not correct, update your code to return the correct product`,
      options
    ).toBe("Product 2");
    expect(
      res.req.method,
      `When calling GET /api/v1/product/:slug, the method should be "GET", but it was "${res.req.method}", update the method to be "GET"`,
      options
    ).toBe("GET");
    expect(
      res.type,
      `When calling GET /api/v1/product/:slug, the content type should be "application/json", but it was "${res.type}", update the content type to be "application/json"`,
      options
    ).toBe("application/json");
  });

  it("should return no product", async () => {
    const res = await request(app).get("/api/v1/product/product-3");
    expect(
      res.statusCode,
      `The status code should be "404" because there is no product, but it was "${res.statusCode}", update the status code to be 404 when there is no product found`,
      options
    ).toBe(404);
    expect(
      res.body.message,
      `The message should be "No product found" because there is no product, but it was "${res.body.message}", update the message to be "No product found" when there is no product found`,
      options
    ).toBe("No product found");
  });

  it("should return error 500 if the database disconnected", async () => {
    await disconnectDB().then(async () => {
      const res = await request(app).get("/api/v1/product/product-2");
      expect(
        res.statusCode,
        `The application should return 500 for the status code if the database is disconnected`,
        options
      ).toBe(500);
      await connectDB(process.env.MONGODB_URI_TEST);
    });
  });
});

describe("GET /api/v1/products with filters", () => {
  it("should not return any products", async () => {
    const formData = {
      search: "John Doe",
    };
    const res = await request(app).get("/api/v1/products").query(formData);
    expect(
      res.statusCode,
      `When applying search filters, if there are no products matching the search query, the status code should be "404". but it was "${res.statusCode}", update your code to return "404" when there are no products matching the search query`,
      options
    ).toBe(404);
    expect(
      res.body.message,
      `When applying search filters, if there are no products matching the search query, the message should be "No products found". but it was "${res.body.message}", update your code to return "No products found" when there are no products matching the search query`,
      options
    ).toBe("No products found");
  });

  it("should return one products that fits the minimum price", async () => {
    const formData = {
      price: {
        minPrice: 200,
      },
    };
    const res = await request(app).get("/api/v1/products").query(formData);
    expect(
      res.statusCode,
      `When applying price filters, if there are products matching the price query, the status code should be "200". but it was "${res.statusCode}", update your code to return "200" when there are products matching the price query`,
      options
    ).toBe(200);
    expect(
      res.body.message,
      `When applying price filters, if there are products matching the price query, the message should be "Products found". but it was "${res.body.message}", update your code to return "Products found" when there are products matching the price query`,
      options
    ).toBe("Products found");
    expect(
      res.body.products.length,
      `When applying price filters, if there are products matching the price query, the length of the products should the same with the products that match the price query, update your code to return the correct amount of products`,
      options
    ).toBe(1);
    expect(
      res.body.products[0].price,
      `When applying price filters, if there are products matching the price query, the price of the products should be greater than or equal to the minimum price, update your code to return the correct price of the products`,
      options
    ).toBeGreaterThanOrEqual(200);
  });

  it("should return two products that fits the maximum price", async () => {
    const formData = {
      price: {
        maxPrice: 1000,
      },
    };
    const res = await request(app).get("/api/v1/products").query(formData);
    expect(
      res.statusCode,
      `When applying price filters, if there are products matching the price query, the status code should be "200". but it was "${res.statusCode}", update your code to return "200" when there are products matching the price query`,
      options
    ).toBe(200);
    expect(
      res.body.message,
      `When applying price filters, if there are products matching the price query, the message should be "Products found". but it was "${res.body.message}", update your code to return "Products found" when there are products matching the price query`,
      options
    ).toBe("Products found");
    expect(
      res.body.products.length,
      `When applying price filters, if there are products matching the price query, the length of the products should the same with the products that match the price query, update your code to return the correct amount of products`,
      options
    ).toBe(2);
    expect(
      res.body.products[0].price,
      `When applying price filters, if there are products matching the price query, the price of the products should be less than or equal to the maximum price, update your code to return the correct price of the products`,
      options
    ).toBeLessThanOrEqual(1000);
  });

  it("should return products", async () => {
    const formData = {
      search: "Product",
      price: {
        minPrice: 200,
        maxPrice: 1000,
      },
    };
    const res = await request(app).get("/api/v1/products").query(formData);
    expect(
      res.statusCode,
      `When applying search and price filters, if there are products matching the search and price query, the status code should be "200". but it was "${res.statusCode}", update your code to return "200" when there are products matching the search and price query`,
      options
    ).toBe(200);
    expect(
      res.body.message,
      `When applying search and price filters, if there are products matching the search and price query, the message should be "Products found". but it was "${res.body.message}", update your code to return "Products found" when there are products matching the search and price query`,
      options
    ).toBe("Products found");
    expect(
      res.body.products.length,
      `When applying search and price filters, if there are products matching the search and price query, the length of the products should the same with the products that match the search and price query, update your code to return the correct amount of products`,
      options
    ).toBe(1);
    res.body.products.forEach((product) => {
      expect(
        product.price,
        `When applying search and price filters, if there are products matching the search and price query, the price of the products should be greater than or equal to the minimum price and less than or equal to the maximum price, update your code to return the correct price of the products`,
        options
      ).toBeGreaterThanOrEqual(200);
      expect(
        product.price,
        `When applying search and price filters, if there are products matching the search and price query, the price of the products should be greater than or equal to the minimum price and less than or equal to the maximum price, update your code to return the correct price of the products`,
        options
      ).toBeLessThanOrEqual(1000);
    });
  });
});

afterAll(async () => {
  const collections = await mongoose.connection.db.collections();
  for (let collection of collections) {
    await collection.drop();
  }
  await disconnectDB();
});

async function createProducts() {
  await Product.create(
    {
      name: "Product 1",
      price: 100,
      description: "Description 1",
    },
    {
      name: "Product 2",
      price: 200,
      description: "Description 2",
    }
  );
}

async function connectDB(url) {
  return mongoose.connect(url, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
}

async function disconnectDB() {
  await mongoose.connection.close();
}
