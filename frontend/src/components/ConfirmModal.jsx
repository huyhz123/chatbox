import { createContext, useContext, useState, useCallback } from 'react';

const ConfirmContext = createContext();

export const useConfirm = () => {
  const context = useContext(ConfirmContext);
  if (!context) {
    throw new Error('useConfirm must be used within ConfirmProvider');
  }
  return context;
};

export const ConfirmProvider = ({ children }) => {
  const [confirmState, setConfirmState] = useState({
    isOpen: false,
    title: '',
    message: '',
    confirmText: 'Xác nhận',
    cancelText: 'Hủy',
    type: 'danger', // danger | warning | info
    onConfirm: null,
    onCancel: null
  });

  const confirm = useCallback(({
    title = 'Xác nhận',
    message = 'Bạn có chắc chắn?',
    confirmText = 'Xác nhận',
    cancelText = 'Hủy',
    type = 'danger'
  }) => {
    return new Promise((resolve) => {
      setConfirmState({
        isOpen: true,
        title,
        message,
        confirmText,
        cancelText,
        type,
        onConfirm: () => {
          setConfirmState(prev => ({ ...prev, isOpen: false }));
          resolve(true);
        },
        onCancel: () => {
          setConfirmState(prev => ({ ...prev, isOpen: false }));
          resolve(false);
        }
      });
    });
  }, []);

  const buttonColors = {
    danger: 'bg-red-500 hover:bg-red-600',
    warning: 'bg-yellow-500 hover:bg-yellow-600',
    info: 'bg-blue-500 hover:bg-blue-600'
  };

  return (
    <ConfirmContext.Provider value={confirm}>
      {children}

      {/* Modal */}
      {confirmState.isOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
          {/* Overlay */}
          <div
            className="absolute inset-0 bg-black/50"
            onClick={confirmState.onCancel}
          />

          {/* Modal content */}
          <div className="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 animate-scale-in">
            <h3 className="text-lg font-bold text-dark mb-2">
              {confirmState.title}
            </h3>
            <p className="text-gray-600 mb-6">
              {confirmState.message}
            </p>
            <div className="flex gap-3 justify-end">
              <button
                onClick={confirmState.onCancel}
                className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
              >
                {confirmState.cancelText}
              </button>
              <button
                onClick={confirmState.onConfirm}
                className={`px-4 py-2 text-white rounded-lg transition ${buttonColors[confirmState.type]}`}
              >
                {confirmState.confirmText}
              </button>
            </div>
          </div>
        </div>
      )}
    </ConfirmContext.Provider>
  );
};

export default ConfirmProvider;
