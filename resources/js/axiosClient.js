import axios from 'axios'
const instance = axios.create({
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
})

instance.interceptors.request.use(config => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token
    }
    return config
})

instance.interceptors.response.use(
    res => res,
    err => {
        if (err.response?.status === 419) {
            return axios.get('/sanctum/csrf-cookie', { withCredentials: true }).then(() => {
                return instance(err.config)
            })
        }
        return Promise.reject(err)
    }
)
export default instance