from __future__ import division, print_function

import pandas as pd
import tensorflow as tf

class Segmentifier:
    data_normalizer = None
    model_handler = None
    central_system = None

    def __init__(self, data_normalizer, model_handler, central_system):
        self.data_normalizer = data_normalizer
        self.model_handler = model_handler
        self.central_system = central_system

    def segmentify(self, user_data):
        user_data = [[user_data.get('device_is_mobile'),
                user_data.get('device_manufacturer'),
                user_data.get('location_country_name'),
                user_data.get('location_city_name'),
                user_data.get('device_screen_width'),
                user_data.get('device_screen_height'),
                user_data.get('segment_id'),
                user_data.get('device_product'),
                user_data.get('browser_name')]]

        # fontos hogy a sorrend ugyanaz legyen, mint assets/user_data.csv -ben es kf-admin recommender service -ben
        user_data = pd.DataFrame(user_data, columns=['device_is_mobile',
                                         'device_manufacturer',
                                         'location_country_name',
                                         'location_city_name',
                                         'device_screen_width',
                                         'device_screen_height',
                                         'segment_id',
                                         'device_product',
                                         'browser_name'])

        user_data = self.data_normalizer.normalize_dataframe(user_data)
        # user_data.drop(columns=['segment_id'], inplace=True, axis=1, errors='ignore')

        # dataframe = self.central_system.getUserData()
        dataframe = pd.read_csv('assets/user_data.csv')
        dataframe = self.data_normalizer.normalize_dataframe(dataframe)

        print('USER DATA')
        print(user_data)

        print('DATAFRAME')
        print(dataframe)

        feature_columns = self.model_handler.get_feature_columns()
        num_of_outputs = self.central_system.getNextSequence()
        model = self.model_handler.compile_model(feature_columns, dataframe, num_of_outputs)

        new_user = [
            [user_data.iloc[0]['device_price']],
            [user_data.iloc[0]['location_city_name']],
            [user_data.iloc[0]['device_manufacturer']],
            [user_data.iloc[0]['device_product']],
            [user_data.iloc[0]['browser_name']]
        ]
        return model.predict(new_user)

    def train_model(self):
        dataframe = self.central_system.getUserData()
        dataframe = self.data_normalizer.normalize_dataframe(dataframe)

        # dataframe = pd.read_csv('assets/user_data.csv')
        # dataframe = self.data_normalizer.normalize_dataframe(dataframe)
        feature_columns = self.model_handler.get_feature_columns()
        num_of_outputs = self.central_system.getNextSequence()
        model = self.model_handler.train_model(feature_columns, dataframe, num_of_outputs)
