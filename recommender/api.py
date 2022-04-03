# -*- coding: utf-8 -*-

from flask import Flask
from services.recommender import Recommender
from services.text_normalizer import TextNormalizer
from models.product import Product
from models.interaction import Interaction
from controllers.recommender_controller import RecommenderController
from services.math import softmax
from services.segmentifier import Segmentifier
from services.model_handler import ModelHandler
from services.data_normalizer import DataNormalizer
from services.central_system import CentralSystem
from services.config import read_section

app = Flask(__name__)

product_model = Product()
interaction_model = Interaction()

text_normalizer = TextNormalizer()
recommender = Recommender(text_normalizer)

config = read_section('central')
central_system = CentralSystem(config)

data_normalizer = DataNormalizer()
model_handler = ModelHandler(data_normalizer)
segmentifier = Segmentifier(data_normalizer, model_handler, central_system)

controller = RecommenderController(product_model, interaction_model, recommender, segmentifier)

@app.route("/segmentify", methods=['POST'])
def segmentify():
    return controller.segmentify()

@app.route("/recommend-batch", methods=['POST'])
def recommend():
    print('recommend batch')
    return controller.recommend_batch()

@app.route("/train-model", methods=['POST'])
def train_model():
    return controller.train_model()

@app.route("/test", methods=['GET'])
def test():
    products = product_model.getAll()
    print(products)
    return 'OK'

    # new_user = get_test_user("Austria", "Vienna", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("2.")
    # print(predict)

    # new_user = get_test_user("Russia", "Moscow", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("3.")
    # print(predict)

    # new_user = get_test_user("United Kingdom", "Abergele", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("4.")
    # print(predict)

    # new_user = get_test_user("Hungary", "Budapest", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("5.")
    # print(predict)

    # new_user = get_test_user("United Kingdom", "London", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("6.")
    # print(predict)

    # new_user = get_test_user("Austria", "Graz", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("7.")
    # print(predict)

    # new_user = get_test_user("Netherlands", "Emmen", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("8.")
    # print(predict)

    # new_user = get_test_user("Germany", "Munich", "other")
    # predict = segmentifier.segmentify(new_user)
    # print("9.")
    # print(predict)

    # new_user = get_test_user("Italy", "Milan", "expensive")
    # predict = segmentifier.segmentify(new_user)
    # print("10.")
    # print(predict)

    # new_user = get_test_user("Netherlands", "Amsterdam", "expensive")
    # predict = segmentifier.segmentify(new_user)
    # print("11.")
    # print(predict)

    # new_user = get_test_user("Netherlands", "Emmen", "cheap")
    # predict = segmentifier.segmentify(new_user)
    # print("12.")
    # print(predict)

    return 'ok'

def get_test_user(county, city, device_price):
    switcher = {
        "expensive": { "device_manufacturer": "Apple", "device_screen_width": "332", "device_screen_height": "667"},
        "cheap": { "device_manufacturer": "Xiaomi", "device_screen_width": "280", "device_screen_height": "580"},
        "other": { "device_manufacturer": "Samsung", "device_screen_width": "290", "device_screen_height": "590"},
    }

    device = switcher.get(device_price, "other")

    return {
        "location_country_name": county,
        "location_city_name": city,
        "device_is_mobile": 1,
        "device_manufacturer": device.get("device_manufacturer"),
        "device_screen_width": device.get("device_screen_width"),
        "device_screen_height": device.get("device_screen_height")
    }
