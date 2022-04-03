import tensorflow as tf

class DataNormalizer:
    def df_to_dataset(self, dataframe, shuffle=True, batch_size=32):
        dataframe = dataframe.copy()
        labels = dataframe.pop('segment_id')
        ds = tf.data.Dataset.from_tensor_slices((dict(dataframe), labels))

        if shuffle:
            ds = ds.shuffle(buffer_size=len(dataframe))

        ds = ds.batch(batch_size)

        return ds

    def normalize_dataframe(self, dataframe):
        drop_columns = ['created_at', 'connection_effective_type', 'connection_ip_address', 'browser_version', 'browser_language', 'connection_real_type', 'timezone_offset', 'location_postal_code', 'location_subdivision_name', 'location_subdivision_code', 'location_latitude', 'location_longitude', 'os_version', 'location_country_code', 'updated_at', 'email_domain', 'phone_provider', 'birth_date', 'sex', 'location_real_postal_code', 'user_id', 'partner_external_id', 'cookie_id', 'id', 'os_architecture', 'connection_bandwidth', 'device_memory', 'os_name']
        dataframe.drop(columns=drop_columns, inplace=True, axis=1, errors='ignore')

        dataframe = self.fill_null_values(dataframe)
        dataframe = self.fill_device_price(dataframe)

        drop_columns =  ['device_screen_width', 'device_screen_height', 'device_is_mobile']
        dataframe.drop(columns=drop_columns, inplace=True, axis=1, errors='ignore')

        return dataframe

    def fill_device_price(self, dataframe):
        for i,row in dataframe.iterrows():
            if row['device_manufacturer'] == 'Apple' and row['device_is_mobile'] == 1:
                # minden Plus jelzesu, 7,8,X,XS
                if row['device_screen_width'] >= 370 and row['device_screen_height'] >= 667:
                    dataframe.set_value(i,'device_price','expensive')
                else:
                    dataframe.set_value(i,'device_price','cheap')
            else:
                dataframe.set_value(i,'device_price','other')

        return dataframe

    def fill_null_values(self, dataframe):
        dataframe.device_manufacturer.fillna(value='', inplace=True)
        dataframe.location_country_name.fillna(value='', inplace=True)
        dataframe.location_city_name.fillna(value='', inplace=True)
        dataframe.browser_name.fillna(value='', inplace=True)
        dataframe.device_manufacturer.fillna(value='', inplace=True)
        dataframe.device_product.fillna(value='', inplace=True)

        # Egyeb
        dataframe.segment_id.fillna(value=6, inplace=True)

        return dataframe
