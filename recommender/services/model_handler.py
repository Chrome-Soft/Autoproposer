import os
os.environ["CUDA_VISIBLE_DEVICES"]="-1"

import tensorflow as tf
from tensorflow.keras import layers
from sklearn.model_selection import train_test_split
from tensorflow import feature_column

# tf.compat.v1.enable_eager_execution()
tf.enable_eager_execution()
# sess = tf.Session(config=tf.ConfigProto(device_count={'GPU': 0}))

class ModelHandler:
    data_normalizer = None

    def __init__(self, data_normalizer):
        self.data_normalizer = data_normalizer

    def get_feature_columns(self):
        feature_columns = []

        #country_hashed = feature_column.categorical_column_with_hash_bucket(
        #    'location_country_name', 175)
        #country_one_hot = feature_column.indicator_column(country_hashed)
        #feature_columns.append(country_one_hot)

        device_manufacturer_hashed = feature_column.categorical_column_with_hash_bucket('device_manufacturer', 30)
        device_manufacturer_embedded = feature_column.embedding_column(device_manufacturer_hashed, dimension=30)
        feature_columns.append(device_manufacturer_embedded)

        # ebbol tul sok van
        device_product_hashed = feature_column.categorical_column_with_hash_bucket('device_product', 700)
        device_product_embedded = feature_column.embedding_column(device_product_hashed, dimension=700)
        feature_columns.append(device_product_embedded)

        browswe_name_hashed = feature_column.categorical_column_with_hash_bucket('browser_name', 40)
        browser_name_embedded = feature_column.embedding_column(browswe_name_hashed, dimension=40)
        feature_columns.append(browser_name_embedded)

        # city_hashed = feature_column.categorical_column_with_hash_bucket(
            # 'location_city_name', 1296)
        # city_embedding = feature_column.embedding_column(city_hashed, dimension=1296**0.25)
        # feature_columns.append(city_embedding)

        device_price = feature_column.categorical_column_with_vocabulary_list(
            'device_price', ['expensive', 'cheap', 'other'])
        device_price_one_hot = feature_column.indicator_column(device_price)
        feature_columns.append(device_price_one_hot)

        return feature_columns

    def compile_model(self, feature_columns, dataframe, num_of_outputs):
        train, test = train_test_split(dataframe, test_size=0.2)
        train, val = train_test_split(train, test_size=0.2)

        print(len(train), 'train examples')
        print(len(val), 'val examples')
        print(len(test), 'test examples')

        feature_layer = layers.DenseFeatures(feature_columns)

        train_ds = self.data_normalizer.df_to_dataset(train)
        val_ds = self.data_normalizer.df_to_dataset(val, shuffle=False)
        test_ds = self.data_normalizer.df_to_dataset(test, shuffle=False)

        model = tf.keras.Sequential([
            feature_layer,
            layers.Dense(128, activation='relu'),
            layers.Dense(128, activation='relu'),
            layers.Dense(num_of_outputs, activation='softmax'),
        ])

        model.compile(optimizer='adam',
                      loss='sparse_categorical_crossentropy',
                      metrics=['accuracy'],
                      run_eagerly=True)

        checkpoint_path = "training/cp.ckpt"
        model.load_weights(checkpoint_path)
        loss, acc = model.evaluate(test_ds)

        return model

    def train_model(self, feature_columns, dataframe, num_of_outputs):
        train, test = train_test_split(dataframe, test_size=0.2)
        train, val = train_test_split(train, test_size=0.2)

        print(len(train), 'train examples')
        print(len(val), 'val examples')
        print(len(test), 'test examples')

        feature_layer = layers.DenseFeatures(feature_columns)

        train_ds = self.data_normalizer.df_to_dataset(train)
        val_ds = self.data_normalizer.df_to_dataset(val, shuffle=False)
        test_ds = self.data_normalizer.df_to_dataset(test, shuffle=False)

        model = tf.keras.Sequential([
            feature_layer,
            layers.Dense(128, activation='relu'),
            layers.Dense(128, activation='relu'),
            layers.Dense(num_of_outputs, activation='softmax'),
        ])

        model.compile(optimizer='adam',
                      loss='sparse_categorical_crossentropy',
                      metrics=['accuracy'],
                      run_eagerly=True)

        checkpoint_path = "training/cp.ckpt"
        cp_callback = tf.keras.callbacks.ModelCheckpoint(filepath=checkpoint_path,
                                                         save_weights_only=True,
                                                         verbose=1, period=1)

        model.fit(train_ds, validation_data=val_ds, epochs=5, callbacks=[cp_callback])
        loss, acc = model.evaluate(test_ds)
        print('Accuracy:', acc)

        return model
