const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const bcrypt = require("bcryptjs");

require("dotenv").config();
mongoose.set("strictQuery", true);

const options = {
  showPrefix: false,
  showMatcherMessage: true,
  showStack: true,
};

const user = {
  name: "testnameforuser",
  username: "testusernameforuser",
  email: "testemailfor@user.com",
  password: "testpasswordforuser",
  confirmPassword: "testpasswordforuser",
};

let token;
let returnedUser;

beforeAll(async () => {
  await connectDB().then(
    async () => {
      user.password = bcrypt.hashSync(user.password, bcrypt.genSaltSync(10));
      await mongoose.connection.collection("users").insertOne(user);
      user.password = "testpasswordforuser";
      console.log("Database connected successfully");
    },
    (err) => {
      console.log("There is problem while connecting database " + err);
    }
  );
});

describe("Testing DELETE /api/v1/profile", () => {
  it("should login a user with username", async () => {
    const res = await request(app).post("/api/v1/login").send({
      username: user.username,
      password: user.password,
    });
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the POST /api/v1/login route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response body should have a property called "token", but it doesn't, change the response body in the function that handles the POST /api/v1/login route`,
      options
    ).toHaveProperty("token");
    token = res.body.token;
  });

  it("should delete a user", async () => {
    const res = await request(app)
      .delete("/api/v1/profile")
      .set("Authorization", `Bearer ${token}`);
    expect(
      res.statusCode,
      `The status code should be 200, but it is "${res.statusCode}", change the status code in the function that handles the DELETE /api/v1/profile route`,
      options
    ).toBe(200);
    expect(
      res.body,
      `The response body should have a property called "message", but it doesn't, change the response body in the function that handles the DELETE /api/v1/profile route`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response body should have a property called "message" with the value "User Deleted Successfully", but it doesn't, change the response body in the function that handles the DELETE /api/v1/profile route`,
      options
    ).toBe("User Deleted Successfully");
  });
  it("should not delete a user if the user does't exist", async () => {
    const res = await request(app)
      .delete("/api/v1/profile")
      .set("Authorization", `Bearer ${token}`);
    expect(
      res.statusCode,
      `The status code should be 400, but it is "${res.statusCode}", change the status code in the function that handles the DELETE /api/v1/profile route`,
      options
    ).toBe(400);
    expect(
      res.body,
      `The response body should have a property called "message", but it doesn't, change the response body in the function that handles the DELETE /api/v1/profile route`,

      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `The response body should have a property called "message" with the value "User Delete Failed", but it doesn't, change the response body in the function that handles the DELETE /api/v1/profile route`,
      options
    ).toBe("User Delete Failed");
  });
});

afterAll(async () => {
  const collections = await mongoose.connection.db.collections();
  for (let collection of collections) {
    await collection.drop();
  }
  await disconnectDB();
});

async function connectDB() {
  return mongoose.connect(process.env.MONGODB_URI_TEST, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
}

async function disconnectDB() {
  await mongoose.connection.close();
}
