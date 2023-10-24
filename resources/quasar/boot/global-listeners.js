const GLOBAL_LISTENERS = {
  onKeypressOnlyUnsignedNumber(e) {
    const keyCode = (e.keyCode ? e.keyCode : e.which);
    console.log(keyCode)
    if ((keyCode < 48 || keyCode > 57)) {
      e.preventDefault();
    }
  },
  onKeypressOnlyNumber(e) {
    const keyCode = (e.keyCode ? e.keyCode : e.which);
    if ((keyCode < 48 || keyCode > 57) && (keyCode === 46 || keyCode === 45)) {
      e.preventDefault();
    }
  },
  onKeypressOnlyFloat(e) {
    const keyCode = (e.keyCode ? e.keyCode : e.which);
    if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
      e.preventDefault();
    }
  },
  onKeypressMaxLength(e, max) {
    const len = e && e.target ? String(e.target.value).length : 0;

    if (len >= max) {
      e.preventDefault();
    }
  },
  onKeypressDisabled(e) {
    e.preventDefault();
  },
  onKeyupDisabled(e) {
    e.preventDefault();
  }
}

export default ({ Vue }) => {
  Vue.prototype.$globalListeners = GLOBAL_LISTENERS;
}
