import axios from 'axios'
import { decrypt } from 'src/services/http-crypter'

axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'


class AxiosURLSearchParams {
  constructor(params, options) {
    this._pairs = [];

    params && axios.toFormData(params, this, options);
  }

  append(name, value) {
    this._pairs.push([name, value]);
  }

  toString() {
    return this._pairs.map(function each(pair) {
      return _encodeUri(pair[0]) + '=' + _encodeUri(pair[1]);
    }, '').join('&');
  }
}

function _encodeUri(val) {
  return encodeURIComponent(val).
    replace(/%3A/gi, ':').
    replace(/%24/g, '$').
    replace(/%2C/gi, ',').
    replace(/%20/g, '+').
    replace(/%5B/gi, '[').
    replace(/%5D/gi, ']');
}

function paramsTransformer(params) {
  const isURLSearchParams = Object.prototype.toString.call(params).slice(8, -1) === 'URLSearchParams'

  if (!isURLSearchParams) {
    if (params.table_pagination) {
      const tablePagination = params.table_pagination;

      const sorts = []
      let sortBy = tablePagination.sortBy

      if (sortBy) {
        if (sortBy === 'index') {
          sortBy = 'created_at'
        }

        sorts.push(`${tablePagination.descending ? '-' : ''}${sortBy}`)
      }

      params.sorts = sorts.join('|')

      if (tablePagination.page) {
        params.page = tablePagination.page
      }

      if (tablePagination.rowsPerPage) {
        params.limit = tablePagination.rowsPerPage
      }

      delete params.table_pagination;

      params.sorts = sorts.join('|');
    }

    if (params.table_search) {
      const tableSearch = params.table_search
      let searchOperator = ''

      for (const searchField in tableSearch) {
        if (tableSearch[searchField].value) {
          searchOperator = tableSearch[searchField].operator || ''

          if (searchOperator) {
            searchOperator = searchOperator + ':'
          }

          params[searchField] = searchOperator + tableSearch[searchField].value
        }
      }

      delete params.table_search;
    }
  }

  return params
}

export default ({ Vue }) => {
  let apiUrl = process.env.API_URL || ''
  let appClientId = process.env.API_APP_ID || ''

  if (process.env.CLIENT) {
    try {
      const appData = JSON.parse(decrypt(window._APP_DATA || ''));
      apiUrl = appData.url.api || {}
      appClientId = appData.constants.user_access_token.CLIENT_CONSOLE || ''
    } catch (err) { }
  }


  const instance = axios.create({
    baseURL: apiUrl,
    timeout: 30000,
    paramsTransformer,
    paramsSerializer: function (params, options){
      params = paramsTransformer(params)

      return Object.prototype.toString.call(params).slice(8, -1) === 'URLSearchParams' ?
        params.toString() :
        new AxiosURLSearchParams(params, options).toString(_encodeUri);
    }
  });

  instance.defaults.headers.common['Content-Type'] = 'application/json'
  instance.defaults.headers.common['Accept'] = 'application/json'
  instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
  instance.defaults.headers.common['X-Console-App'] = appClientId

  instance.interceptors.response.use((response) => {
    if (response && response.data && response.data.meta) {
      const meta = response.data.meta || {}
      let sorts = meta.sorts || ''
      let descending = false

      if (sorts.substr(0, 1) === '-') {
        sorts = sorts.substr(1)
        descending = true
      }

      response.data.pagination = {
        rowsPerPage: meta.limit,
        page: meta.page,
        rowsNumber: parseInt(meta.total, 10) || 0
      }

      if (sorts) {
        response.data.pagination.sortBy = sorts
        response.data.pagination.descending = descending
      }
    }

    return response
  }, (error) => {
    if (error.response && error.response.data) {
      if (error.response.data.message) {
        error.message = error.response.data.message
      }

      if (error.response.data.errors) {
        try {
          const dataErrors = error.response.data.errors
          let firstValidationErrorMessage = ''
          const validationErrors = {}

          for (const name in dataErrors) {
            if (typeof dataErrors[name] === 'string') {
              if (!firstValidationErrorMessage) {
                firstValidationErrorMessage = dataErrors[name]
              }

              validationErrors[name] = dataErrors[name]
            } else if (
              Array.isArray(dataErrors[name]) &&
              dataErrors[name].length
            ) {
              if (!firstValidationErrorMessage) {
                firstValidationErrorMessage = dataErrors[name][0]
              }

              validationErrors[name] = dataErrors[name][0]
            }
          }

          error.validation = {
            message: firstValidationErrorMessage,
            errors: validationErrors
          }
        } catch (err) {
          //
        }
      }
    }

    return Promise.reject(error)
  });

  Vue.prototype.$axios = axios
  Vue.prototype.$api = instance
}
