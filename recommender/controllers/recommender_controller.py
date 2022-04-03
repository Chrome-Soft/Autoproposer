from flask import jsonify
from flask import request

class RecommenderController:
    product_model = None
    interaction_model = None
    recommender_service = None
    segmentifier_service = None

    def __init__(self, product_model, interaction_model, recommender_service, segmentifier_service):
        self.product_model = product_model
        self.interaction_model = interaction_model
        self.recommender_service = recommender_service
        self.segmentifier_service = segmentifier_service

    def segmentify(self):
        data = request.get_json()
        user_data = data['user_data']

        predict = self.segmentifier_service.segmentify(user_data)
        return jsonify(predict.tolist())

    def train_model(self):
        self.segmentifier_service.train_model();
        return jsonify('OK')

    def recommend_batch(self):
        data = request.get_json()

        segment_ids = data['segment_ids']
        products = self.product_model.getAll()
        result = {}

        for segment_id in segment_ids:
            interactions = self.interaction_model.getBy(segment_id)
            ids = self.recommender_service.recommend_batch(interactions,products,self.product_model.get_most_popular);
            result[segment_id] = ids

        return jsonify(result)

