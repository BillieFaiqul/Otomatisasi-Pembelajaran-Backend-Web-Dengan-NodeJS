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

describe("POST /api/v1/product", () => {
  it("should create a product", async () => {
    const res = await request(app).post("/api/v1/product").send({
      name: "Product 3",
      price: 1009,
      description: "Description 3",
    });
    expect(
      res.statusCode,
      `Expected status code "201", but got "${res.statusCode}", the "201" is the status code for "Created" and it is the status code that we are expecting to get back from the server when we create a new product.`,
      options
    ).toBe(201);
    expect(
      res.body,
      `Expected the response body to have a property called "message" and the value of that property should be "Product created"`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Product created"`,
      options
    ).toBe("Product created");

    expect(
      res.body,
      `Expected the response body to have a property called "product" and the value of that property should be an object`,
      options
    ).toHaveProperty("product");
    expect(
      res.body.product.name,
      `The value of the object returned doesn't match the value of the "name" property that we sent to the server.`,
      options
    ).toBe("Product 3");

    expect(
      res.req.method,
      `Expected the request method to be "POST"`,
      options
    ).toBe("POST");
    expect(
      res.type,
      `Expected the response type to be "application/json"`,
      options
    ).toBe("application/json");
  });

  it("should not create a product because it already exists", async () => {
    const res = await request(app).post("/api/v1/product").send({
      name: "Product 3",
      price: 1009,
      description: "Description 3",
    });
    expect(
      res.statusCode,
      `Expected status code "409", but got "${res.statusCode}", the "409" is the status code for "Conflict" and it is the status code that we are expecting to get back from the server when we try to create a product that already exists.`,
      options
    ).toBe(409);
    expect(
      res.body,
      `Expected the response body to have a property called "message" and the value of that property should be "Product already exists"`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Product already exists"`,
      options
    ).toBe("Product already exists");
    expect(
      res.body,
      `Expected the response body to have a property called "product" for the existed product and the value of that property should be an object`,
      options
    ).toHaveProperty("product");
    expect(
      res.body.product.name,
      `The value of the object returned doesn't match the value of the "name" property that we sent to the server.`,
      options
    ).toBe("Product 3");
  });

  it("should not create a product because of the name is not provided", async () => {
    const res = await request(app).post("/api/v1/product").send({
      name: "",
      price: 1009,
      description: "Description 3",
    });
    expect(
      res.statusCode,
      `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to create a product without providing the name.`,
      options
    ).toBe(500);
    expect(
      res.body.errors.name.message,
      `Expected the value of the "message" property to be "Name is required", but got "${res.body.errors.name.message}" instead. Change the validation property of the "name" property in the "product.model.js" file to "required: true" and then run the test again.`,
      options
    ).toBe("Name is required");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Product validation failed: name: Name is required", but got "${res.body.message}" instead. Change the validation property of the "name" property in the "product.model.js" file to "required: true" and then run the test again.`,
      options
    ).toContain("Name is required");
  });

  it("should not create a product because of the price is not provided", async () => {
    const res = await request(app).post("/api/v1/product").send({
      name: "Product 4",
      price: "",
      description: "Description 4",
    });
    expect(
      res.statusCode,
      `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to create a product without providing the price.`,
      options
    ).toBe(500);
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Cast to Number failed" for value "" at path "price" for model "Product", but got "${res.body.message}" instead. Change the validation property of the "price" property in the "product.model.js" file to "required: true" and then run the test again.`,
      options
    ).toContain("Cast to Number failed");
  });

  it("should not create a product because of the description is not provided", async () => {
    const res = await request(app).post("/api/v1/product").send({
      name: "Product 4",
      price: 1009,
      description: "",
    });
    expect(
      res.statusCode,
      `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to create a product without providing the description.`,
      options
    ).toBe(500);
    expect(
      res.body.errors.description.message,
      `Expected the value of the "message" property to be "Description is required", but got "${res.body.errors.description.message}" instead. Change the validation property of the "description" property in the "product.model.js" file to "required: true" and then run the test again.`,
      options
    ).toBe("Description is required");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Product validation failed: description: Description is required", but got "${res.body.message}" instead. Change the validation property of the "description" property in the "product.model.js" file to "required: true" and then run the test again.`,
      options
    ).toContain("Description is required");
  });

  it("should not create a product because of the price is less than 0", async () => {
    const res = await request(app).post("/api/v1/product").send({
      name: "Product 4",
      price: -1009,
      description: "Description 4",
    });
    expect(
      res.statusCode,
      `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to create a product with a price less than 0.
    `,
      options
    ).toBe(500);
    expect(
      res.body.errors.price.message,
      `Expected the value of the "message" property to be "Price must be greater than 0", but got "${res.body.errors.price.message}" instead. Change the validation property of the "price" property in the "product.model.js" file to "min: 0" and then run the test again.`,
      options
    ).toBe("Price must be greater than 0");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Price must be greater than 0", but got "${res.body.message}" instead. Change the validation property of the "price" property in the "product.model.js" file to "min: 0" and then run the test again.`,
      options
    ).toContain("Price must be greater than 0");
  });

  it("should return error 500", async () => {
    await disconnectDB().then(async () => {
      const res = await request(app).post("/api/v1/product").send({
        name: "Product 4",
        price: 1009,
        description: "Description 4",
      });
      expect(
        res.statusCode,
        `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to create a product without database connection.`,
        options
      ).toBe(500);
      await connectDB();
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

async function connectDB() {
  return mongoose.connect(process.env.MONGODB_URI_TEST, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
}

async function disconnectDB() {
  await mongoose.connection.close();
}
